// lib/shared/models/conversation_model.dart

import 'user_model.dart';

class MessageModel {
  final int id;
  final int conversationId;
  final int senderId;
  final UserModel? sender;
  final String body;
  final String? attachmentName;
  final String? attachmentMime;
  final int? attachmentSize;
  final String formattedAttachmentSize;
  final String? attachmentBlob;
  final bool hasAttachment;
  final bool isImage;
  final bool isPdf;
  final DateTime? createdAt;

  const MessageModel({
    required this.id,
    required this.conversationId,
    required this.senderId,
    this.sender,
    required this.body,
    this.attachmentName,
    this.attachmentMime,
    this.attachmentSize,
    this.formattedAttachmentSize = '0 KB',
    this.attachmentBlob,
    this.hasAttachment = false,
    this.isImage = false,
    this.isPdf = false,
    this.createdAt,
  });

  factory MessageModel.fromJson(Map<String, dynamic> json) {
    return MessageModel(
      id: json['id'] is int
          ? json['id'] as int
          : int.parse(json['id'].toString()),
      conversationId: json['conversation_id'] is int
          ? json['conversation_id'] as int
          : int.parse(json['conversation_id'].toString()),
      senderId: json['sender_id'] is int
          ? json['sender_id'] as int
          : int.parse(json['sender_id'].toString()),
      sender: json['sender'] is Map<String, dynamic>
          ? UserModel.fromJson(json['sender'] as Map<String, dynamic>)
          : null,
      body: json['body'] as String? ?? '',
      attachmentName: json['attachment_name'] as String?,
      attachmentMime: json['attachment_mime'] as String?,
      attachmentSize: json['attachment_size'] as int?,
      formattedAttachmentSize:
          json['formatted_attachment_size'] as String? ?? '0 KB',
      attachmentBlob: json['attachment_blob'] as String?,
      hasAttachment: json['has_attachment'] == true,
      isImage: json['is_image'] == true,
      isPdf: json['is_pdf'] == true,
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at'].toString())
          : null,
    );
  }
  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'conversation_id': conversationId,
      'sender_id': senderId,
      'sender': sender?.toJson(),
      'body': body,
      'attachment_name': attachmentName,
      'attachment_mime': attachmentMime,
      'attachment_size': attachmentSize,
      'formatted_attachment_size': formattedAttachmentSize,
      'attachment_blob': attachmentBlob,
      'has_attachment': hasAttachment,
      'is_image': isImage,
      'is_pdf': isPdf,
      'created_at': createdAt?.toIso8601String(),
    };
  }
}

class ConversationModel {
  final int id;
  /// Normalised conversation type used by the mobile UI tabs.
  /// The backend may return 'team', 'group', 'direct', etc.
  /// [_normalizeType] maps backend values to the UI-expected
  /// 'channel' | 'war_room' | 'direct' taxonomy so that the
  /// messaging tabs always display the correct conversations.
  final String type;
  final String title;
  final String? rawTitle;
  final String? description;
  final bool isPrivate;
  final bool isDirect;
  final int? createdBy;
  final int unreadCount;
  final MessageModel? latestMessage;
  final List<UserModel> participants;
  final DateTime? createdAt;
  final DateTime? updatedAt;

  const ConversationModel({
    required this.id,
    required this.type,
    required this.title,
    this.rawTitle,
    this.description,
    this.isPrivate = false,
    this.isDirect = false,
    this.createdBy,
    this.unreadCount = 0,
    this.latestMessage,
    this.participants = const [],
    this.createdAt,
    this.updatedAt,
  });

  MessageModel? get lastMessage => latestMessage;
  String? get lastMessageAt => latestMessage?.createdAt != null
      ? '${latestMessage!.createdAt!.hour.toString().padLeft(2, '0')}:${latestMessage!.createdAt!.minute.toString().padLeft(2, '0')}'
      : (updatedAt != null
            ? '${updatedAt!.hour.toString().padLeft(2, '0')}:${updatedAt!.minute.toString().padLeft(2, '0')}'
            : null);

  /// Maps backend conversation types to the mobile UI taxonomy.
  ///
  /// The Laravel backend uses 'team' for public group conversations and
  /// 'direct' for 1-on-1 DMs. The mobile MessagingScreen tabs expect
  /// 'channel', 'war_room', or 'direct'. This method bridges the gap.
  static String _normalizeType(String? raw) {
    switch (raw) {
      case 'team':
      case 'group':
      case 'channel':
      case 'public':
        return 'channel';
      case 'war_room':
      case 'incident':
        return 'war_room';
      case 'direct':
      case 'dm':
        return 'direct';
      default:
        // Fallback: treat unknown types as channels so they remain visible
        return 'channel';
    }
  }

  factory ConversationModel.fromJson(Map<String, dynamic> json) {
    final rawType = json['type'] as String? ?? 'team';
    return ConversationModel(
      id: json['id'] is int
          ? json['id'] as int
          : int.parse(json['id'].toString()),
      type: _normalizeType(rawType),
      title: json['title'] as String? ?? 'Channel',
      rawTitle: json['raw_title'] as String?,
      description: json['description'] as String?,
      isPrivate: json['is_private'] == true,
      isDirect: json['is_direct'] == true || rawType == 'direct',
      createdBy: json['created_by'] as int?,
      unreadCount: json['unread_count'] as int? ?? 0,
      latestMessage: json['latest_message'] is Map<String, dynamic>
          ? MessageModel.fromJson(
              json['latest_message'] as Map<String, dynamic>,
            )
          : null,
      participants:
          (json['participants'] as List<dynamic>?)
              ?.map((e) => UserModel.fromJson(e as Map<String, dynamic>))
              .toList() ??
          [],
      createdAt: json['created_at'] != null
          ? DateTime.tryParse(json['created_at'].toString())
          : null,
      updatedAt: json['updated_at'] != null
          ? DateTime.tryParse(json['updated_at'].toString())
          : null,
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'id': id,
      'type': type,
      'title': title,
      'raw_title': rawTitle,
      'description': description,
      'is_private': isPrivate,
      'is_direct': isDirect,
      'created_by': createdBy,
      'unread_count': unreadCount,
      'latest_message': latestMessage?.toJson(),
      'participants': participants.map((p) => p.toJson()).toList(),
      'created_at': createdAt?.toIso8601String(),
      'updated_at': updatedAt?.toIso8601String(),
    };
  }
}
