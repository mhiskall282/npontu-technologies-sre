// lib/features/messaging/messaging_controller.dart

import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/errors/api_exception.dart';
import '../../core/network/api_client.dart';
import '../../core/network/api_client_provider.dart';
import '../../core/services/cache_service.dart';
import '../../shared/models/conversation_model.dart';

class MessagingState {
  final List<ConversationModel> conversations;
  final Map<int, List<MessageModel>> messagesByConversation;
  final bool isLoading;
  final String? errorMessage;

  const MessagingState({
    this.conversations = const [],
    this.messagesByConversation = const {},
    this.isLoading = false,
    this.errorMessage,
  });

  MessagingState copyWith({
    List<ConversationModel>? conversations,
    Map<int, List<MessageModel>>? messagesByConversation,
    bool? isLoading,
    String? errorMessage,
  }) {
    return MessagingState(
      conversations: conversations ?? this.conversations,
      messagesByConversation:
          messagesByConversation ?? this.messagesByConversation,
      isLoading: isLoading ?? this.isLoading,
      errorMessage: errorMessage,
    );
  }
}

class MessagingController extends StateNotifier<MessagingState> {
  final ApiClient _apiClient;
  final CacheService _cacheService;

  MessagingController({
    required ApiClient apiClient,
    required CacheService cacheService,
  }) : _apiClient = apiClient,
       _cacheService = cacheService,
       super(const MessagingState(isLoading: true)) {
    _hydrateCache();
    loadConversations();
  }

  void _hydrateCache() {
    final cached = _cacheService.getList('sre_cache_conversations');
    if (cached != null && cached.isNotEmpty) {
      try {
        final convs = cached
            .map((e) => ConversationModel.fromJson(e as Map<String, dynamic>))
            .toList();
        state = state.copyWith(conversations: convs, isLoading: false);
      } catch (_) {}
    }
  }

  Future<void> loadConversations() async {
    state = state.copyWith(
      isLoading: state.conversations.isEmpty,
      errorMessage: null,
    );

    try {
      final response = await _apiClient.get('/conversations');
      final dataList = response.data['data'] as List<dynamic>? ?? [];

      final convs = dataList
          .map((e) => ConversationModel.fromJson(e as Map<String, dynamic>))
          .toList();

      state = state.copyWith(conversations: convs, isLoading: false);
      await _cacheService.saveList(
        'sre_cache_conversations',
        convs.map((c) => c.toJson()).toList(),
      );
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: state.conversations.isEmpty
            ? 'Failed to load ops communications channels.'
            : null,
      );
    }
  }

  Future<void> loadMessages(int conversationId) async {
    // Check cache first for immediate rendering
    final cacheKey = 'sre_cache_conv_msgs_$conversationId';
    final cachedMsgs = _cacheService.getList(cacheKey);
    if (cachedMsgs != null && cachedMsgs.isNotEmpty) {
      try {
        final parsed = cachedMsgs
            .map((e) => MessageModel.fromJson(e as Map<String, dynamic>))
            .toList();
        final updatedMap = Map<int, List<MessageModel>>.from(
          state.messagesByConversation,
        );
        updatedMap[conversationId] = parsed;
        state = state.copyWith(messagesByConversation: updatedMap);
      } catch (_) {}
    }

    await pollMessages(conversationId);
  }

  /// Active background poll without resetting UI state
  Future<void> pollMessages(int conversationId) async {
    try {
      final response = await _apiClient.get(
        '/conversations/$conversationId/messages',
      );
      final dataList = response.data['data'] as List<dynamic>? ?? [];

      final msgs = dataList
          .map((e) => MessageModel.fromJson(e as Map<String, dynamic>))
          .toList();

      final updatedMap = Map<int, List<MessageModel>>.from(
        state.messagesByConversation,
      );
      updatedMap[conversationId] = msgs;

      state = state.copyWith(messagesByConversation: updatedMap);

      // Persist to offline cache
      await _cacheService.saveList(
        'sre_cache_conv_msgs_$conversationId',
        msgs.map((m) => m.toJson()).toList(),
      );
    } catch (_) {}
  }

  Future<bool> sendMessage({
    required int conversationId,
    required String body,
    String? attachmentName,
    String? attachmentMime,
    int? attachmentSize,
    String? attachmentBlob,
  }) async {
    try {
      final response = await _apiClient.post(
        '/conversations/$conversationId/messages',
        data: {
          'body': body,
          'attachment_name': attachmentName,
          'attachment_mime': attachmentMime,
          'attachment_size': attachmentSize,
          'attachment_blob': attachmentBlob,
        },
      );

      final msgData = response.data['data'] as Map<String, dynamic>;
      final newMsg = MessageModel.fromJson(msgData);

      final updatedMap = Map<int, List<MessageModel>>.from(
        state.messagesByConversation,
      );
      final currentList = updatedMap[conversationId] ?? [];
      final newList = [...currentList, newMsg];
      updatedMap[conversationId] = newList;

      state = state.copyWith(messagesByConversation: updatedMap);

      await _cacheService.saveList(
        'sre_cache_conv_msgs_$conversationId',
        newList.map((m) => m.toJson()).toList(),
      );
      return true;
    } on ApiException catch (e) {
      state = state.copyWith(errorMessage: e.firstErrorMessage);
      return false;
    } catch (_) {
      state = state.copyWith(errorMessage: 'Failed to send message.');
      return false;
    }
  }

  Future<void> markAsRead(int conversationId) async {
    try {
      await _apiClient.post('/conversations/$conversationId/read');
      await loadConversations();
    } catch (_) {}
  }
}

final messagingControllerProvider =
    StateNotifierProvider<MessagingController, MessagingState>((ref) {
      final apiClient = ref.watch(apiClientProvider);
      final cacheService = ref.watch(cacheServiceProvider);
      return MessagingController(
        apiClient: apiClient,
        cacheService: cacheService,
      );
    });
