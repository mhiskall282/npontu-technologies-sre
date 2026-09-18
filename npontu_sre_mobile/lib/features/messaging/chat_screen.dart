// lib/features/messaging/chat_screen.dart

import 'dart:async';
import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/theme/npontu_theme.dart';
import '../../shared/models/conversation_model.dart';
import '../auth/presentation/auth_controller.dart';
import 'messaging_controller.dart';

class ChatScreen extends ConsumerStatefulWidget {
  final int conversationId;

  const ChatScreen({super.key, required this.conversationId});

  @override
  ConsumerState<ChatScreen> createState() => _ChatScreenState();
}

class _ChatScreenState extends ConsumerState<ChatScreen> {
  final _messageController = TextEditingController();
  final _scrollController = ScrollController();
  final _focusNode = FocusNode();
  Timer? _pollTimer;
  int _lastMessageCount = 0;
  bool _isSending = false;

  @override
  void initState() {
    super.initState();

    _focusNode.addListener(() {
      if (_focusNode.hasFocus) {
        Future.delayed(const Duration(milliseconds: 250), () {
          _scrollToBottom();
        });
      }
    });

    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref
          .read(messagingControllerProvider.notifier)
          .loadMessages(widget.conversationId);
      ref
          .read(messagingControllerProvider.notifier)
          .markAsRead(widget.conversationId);

      // Start periodic real-time sync loop (every 3 seconds while active)
      _pollTimer = Timer.periodic(const Duration(seconds: 3), (_) {
        if (mounted) {
          ref
              .read(messagingControllerProvider.notifier)
              .pollMessages(widget.conversationId);
        }
      });
    });
  }

  @override
  void dispose() {
    _pollTimer?.cancel();
    _focusNode.dispose();
    _messageController.dispose();
    _scrollController.dispose();
    super.dispose();
  }

  void _scrollToBottom([int delayMs = 150]) {
    Future.delayed(Duration(milliseconds: delayMs), () {
      if (_scrollController.hasClients) {
        _scrollController.animateTo(
          _scrollController.position.maxScrollExtent,
          duration: const Duration(milliseconds: 250),
          curve: Curves.easeOut,
        );
      }
    });
  }

  Future<void> _send() async {
    final text = _messageController.text.trim();
    if (text.isEmpty) return;

    _messageController.clear();
    setState(() => _isSending = true);

    final success = await ref
        .read(messagingControllerProvider.notifier)
        .sendMessage(conversationId: widget.conversationId, body: text);

    setState(() => _isSending = false);

    if (success) {
      _scrollToBottom(100);
    }
  }

  @override
  Widget build(BuildContext context) {
    final messagingState = ref.watch(messagingControllerProvider);
    final user = ref.watch(authControllerProvider).user;
    final isDark = Theme.of(context).brightness == Brightness.dark;

    final conversation = messagingState.conversations
        .cast<ConversationModel?>()
        .firstWhere((c) => c?.id == widget.conversationId, orElse: () => null);

    final messages =
        messagingState.messagesByConversation[widget.conversationId] ?? [];

    // Trigger auto-scroll when new messages arrive
    if (messages.length != _lastMessageCount) {
      _lastMessageCount = messages.length;
      WidgetsBinding.instance.addPostFrameCallback((_) {
        _scrollToBottom(100);
      });
    }

    return Scaffold(
      resizeToAvoidBottomInset: true,
      appBar: AppBar(
        // Always show back button on chat detail screen
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_rounded),
          tooltip: 'Go Back',
          onPressed: () => Navigator.of(context).pop(),
        ),
        title: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              conversation?.title ?? 'Chat Room #${widget.conversationId}',
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
            Text(
              conversation?.type == 'war_room'
                  ? 'War Room'
                  : (conversation?.type == 'channel'
                        ? 'Team Channel'
                        : 'Direct Chat'),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              style: TextStyle(
                fontSize: 11,
                color: conversation?.type == 'war_room'
                    ? NpontuColors.gold
                    : Colors.white70,
                fontWeight: FontWeight.w600,
              ),
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Sync Messages',
            onPressed: () => ref
                .read(messagingControllerProvider.notifier)
                .loadMessages(widget.conversationId),
          ),
        ],
      ),
      body: SafeArea(
        child: Column(
          children: [
            // Messages list
            Expanded(
              child: messages.isEmpty
                  ? Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          const Icon(
                            Icons.chat_bubble_outline_rounded,
                            size: 48,
                            color: Colors.grey,
                          ),
                          const SizedBox(height: 12),
                          Text(
                            'No messages yet in this room.',
                            style: TextStyle(
                              color: isDark
                                  ? NpontuColors.textSecondaryDark
                                  : NpontuColors.textSecondaryLight,
                            ),
                          ),
                          const SizedBox(height: 4),
                          const Text(
                            'Send the first message or runbook update.',
                            style: TextStyle(fontSize: 12, color: Colors.grey),
                          ),
                        ],
                      ),
                    )
                  : ListView.builder(
                      controller: _scrollController,
                      padding: const EdgeInsets.symmetric(
                        horizontal: 16,
                        vertical: 12,
                      ),
                      itemCount: messages.length,
                      itemBuilder: (ctx, index) {
                        final msg = messages[index];
                        final isMe = msg.senderId == user?.id;
                        return _buildMessageBubble(msg, isMe, isDark);
                      },
                    ),
            ),

            // Message input bar — uses bottom padding from MediaQuery
            // to stay above system navigation gestures and keyboard
            Container(
              padding: EdgeInsets.only(
                left: 12,
                right: 12,
                top: 8,
                // Extra bottom padding ensures the input never hides
                // behind bottom nav gestures on modern Android/iOS
                bottom: 8 + MediaQuery.of(context).padding.bottom,
              ),
              decoration: BoxDecoration(
                color: isDark ? NpontuColors.surfaceMid : Colors.white,
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withAlpha(20),
                    offset: const Offset(0, -1),
                    blurRadius: 4,
                  ),
                ],
              ),
              child: Row(
                children: [
                  IconButton(
                    icon: const Icon(
                      Icons.attach_file_rounded,
                      color: NpontuColors.green,
                    ),
                    tooltip: 'Attach Screenshot / Runbook snippet',
                    onPressed: () {
                      ScaffoldMessenger.of(context).showSnackBar(
                        const SnackBar(
                          content: Text(
                            'Attachment capability ready for runbook uploads.',
                          ),
                        ),
                      );
                    },
                  ),
                  Expanded(
                    child: TextField(
                      controller: _messageController,
                      focusNode: _focusNode,
                      textInputAction: TextInputAction.send,
                      maxLines: 4,
                      minLines: 1,
                      onSubmitted: (_) => _send(),
                      decoration: InputDecoration(
                        hintText: 'Type an ops update or paste logs...',
                        contentPadding: const EdgeInsets.symmetric(
                          horizontal: 16,
                          vertical: 10,
                        ),
                        border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(24),
                          borderSide: BorderSide.none,
                        ),
                        filled: true,
                        fillColor: isDark
                            ? NpontuColors.surfaceDark
                            : const Color(0xFFF3F4F6),
                      ),
                    ),
                  ),
                  const SizedBox(width: 8),
                  IconButton(
                    icon: _isSending
                        ? const SizedBox(
                            width: 20,
                            height: 20,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              color: NpontuColors.green,
                            ),
                          )
                        : const Icon(
                            Icons.send_rounded,
                            color: NpontuColors.green,
                          ),
                    onPressed: _isSending ? null : _send,
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildMessageBubble(MessageModel msg, bool isMe, bool isDark) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        mainAxisAlignment: isMe
            ? MainAxisAlignment.end
            : MainAxisAlignment.start,
        crossAxisAlignment: CrossAxisAlignment.end,
        children: [
          if (!isMe) ...[
            CircleAvatar(
              radius: 14,
              backgroundColor: NpontuColors.green.withAlpha(50),
              child: Text(
                (msg.sender?.name.isNotEmpty == true)
                    ? msg.sender!.name[0].toUpperCase()
                    : 'U',
                style: const TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.bold,
                  color: NpontuColors.green,
                ),
              ),
            ),
            const SizedBox(width: 8),
          ],
          Flexible(
            child: Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: isMe
                    ? NpontuColors.green
                    : (isDark
                          ? NpontuColors.surfaceMid
                          : const Color(0xFFE5E7EB)),
                borderRadius: BorderRadius.only(
                  topLeft: const Radius.circular(16),
                  topRight: const Radius.circular(16),
                  bottomLeft: Radius.circular(isMe ? 16 : 4),
                  bottomRight: Radius.circular(isMe ? 4 : 16),
                ),
              ),
              child: Column(
                crossAxisAlignment: isMe
                    ? CrossAxisAlignment.end
                    : CrossAxisAlignment.start,
                children: [
                  if (!isMe && msg.sender != null) ...[
                    Text(
                      '${msg.sender!.name} • ${msg.sender!.gradeLabel}',
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.w700,
                        color: isDark
                            ? NpontuColors.gold
                            : const Color(0xFFB45309),
                      ),
                    ),
                    const SizedBox(height: 4),
                  ],
                  Text(
                    msg.body,
                    style: TextStyle(
                      fontSize: 14,
                      color: isMe
                          ? Colors.white
                          : (isDark ? Colors.white : Colors.black87),
                    ),
                  ),
                  if (msg.attachmentBlob != null &&
                      msg.attachmentBlob!.isNotEmpty) ...[
                    const SizedBox(height: 8),
                    _buildAttachmentPreview(msg),
                  ],
                  const SizedBox(height: 4),
                  Text(
                    msg.createdAt != null
                        ? '${msg.createdAt!.hour.toString().padLeft(2, '0')}:${msg.createdAt!.minute.toString().padLeft(2, '0')}'
                        : '',
                    style: TextStyle(
                      fontSize: 10,
                      color: isMe ? Colors.white.withAlpha(180) : Colors.grey,
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildAttachmentPreview(MessageModel msg) {
    if (msg.attachmentMime?.startsWith('image/') == true) {
      try {
        final bytes = base64Decode(msg.attachmentBlob!);
        return ClipRRect(
          borderRadius: BorderRadius.circular(8),
          child: Image.memory(
            bytes,
            height: 160,
            width: double.infinity,
            fit: BoxFit.cover,
          ),
        );
      } catch (_) {}
    }

    return Container(
      padding: const EdgeInsets.all(8),
      decoration: BoxDecoration(
        color: Colors.black.withAlpha(20),
        borderRadius: BorderRadius.circular(6),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          const Icon(Icons.insert_drive_file_rounded, size: 16),
          const SizedBox(width: 6),
          Flexible(
            child: Text(
              msg.attachmentName ?? 'Attachment',
              style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600),
              overflow: TextOverflow.ellipsis,
            ),
          ),
        ],
      ),
    );
  }
}
