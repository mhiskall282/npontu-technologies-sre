// lib/features/messaging/messaging_controller.dart

import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/errors/api_exception.dart';
import '../../core/network/api_client.dart';
import '../../core/network/api_client_provider.dart';
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

  MessagingController({required ApiClient apiClient})
    : _apiClient = apiClient,
      super(const MessagingState(isLoading: true)) {
    loadConversations();
  }

  Future<void> loadConversations() async {
    state = state.copyWith(isLoading: true, errorMessage: null);

    try {
      final response = await _apiClient.get('/conversations');
      final dataList = response.data['data'] as List<dynamic>? ?? [];

      final convs = dataList
          .map((e) => ConversationModel.fromJson(e as Map<String, dynamic>))
          .toList();

      state = state.copyWith(conversations: convs, isLoading: false);
    } catch (e) {
      state = state.copyWith(
        isLoading: false,
        errorMessage: 'Failed to load ops communications channels.',
      );
    }
  }

  Future<void> loadMessages(int conversationId) async {
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
      updatedMap[conversationId] = [...currentList, newMsg];

      state = state.copyWith(messagesByConversation: updatedMap);
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
      return MessagingController(apiClient: apiClient);
    });
