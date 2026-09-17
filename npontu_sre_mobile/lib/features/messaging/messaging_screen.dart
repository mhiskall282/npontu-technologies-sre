// lib/features/messaging/messaging_screen.dart

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/theme/npontu_theme.dart';
import '../../shared/models/conversation_model.dart';
import '../../shared/widgets/app_drawer.dart';
import '../../shared/widgets/empty_state.dart';
import '../../shared/widgets/error_retry.dart';
import '../../shared/widgets/skeleton_loader.dart';
import '../team/team_controller.dart';
import 'messaging_controller.dart';

class MessagingScreen extends ConsumerStatefulWidget {
  const MessagingScreen({super.key});

  @override
  ConsumerState<MessagingScreen> createState() => _MessagingScreenState();
}

class _MessagingScreenState extends ConsumerState<MessagingScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 3, vsync: this);
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  void _openCreateConversationDialog() {
    final titleController = TextEditingController();
    String type = 'channel';
    int? participantId;

    showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (context, setDialogState) {
          final members = ref.watch(teamControllerProvider).members;

          return AlertDialog(
            title: const Text('New Communication Channel'),
            content: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  DropdownButtonFormField<String>(
                    value: type,
                    isExpanded: true,
                    decoration: const InputDecoration(
                      labelText: 'Channel Type',
                    ),
                    items: const [
                      DropdownMenuItem(
                        value: 'channel',
                        child: Text('Team Ops Channel'),
                      ),
                      DropdownMenuItem(
                        value: 'war_room',
                        child: Text('Incident War Room'),
                      ),
                      DropdownMenuItem(
                        value: 'direct',
                        child: Text('Direct Message'),
                      ),
                    ],
                    onChanged: (val) {
                      if (val != null) setDialogState(() => type = val);
                    },
                  ),
                  const SizedBox(height: 14),
                  if (type != 'direct') ...[
                    TextField(
                      controller: titleController,
                      decoration: InputDecoration(
                        labelText: type == 'war_room'
                            ? 'War Room Name (INC-XXX)'
                            : 'Channel Name',
                        hintText: type == 'war_room'
                            ? 'INC-8891 DB Latency Spike'
                            : 'sre-deployments',
                      ),
                    ),
                  ] else ...[
                    DropdownButtonFormField<int?>(
                      value: participantId,
                      isExpanded: true,
                      decoration: const InputDecoration(
                        labelText: 'Select Teammate',
                      ),
                      items: members
                          .map(
                            (m) => DropdownMenuItem(
                              value: m.id,
                              child: Text(
                                '${m.name} (${m.gradeLabel})',
                                overflow: TextOverflow.ellipsis,
                              ),
                            ),
                          )
                          .toList(),
                      onChanged: (val) =>
                          setDialogState(() => participantId = val),
                    ),
                  ],
                ],
              ),
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(ctx),
                child: const Text('Cancel'),
              ),
              ElevatedButton(
                style: ElevatedButton.styleFrom(
                  backgroundColor: NpontuColors.green,
                  foregroundColor: Colors.white,
                ),
                onPressed: () async {
                  Navigator.pop(ctx);
                  // Trigger API to create conversation
                  try {
                    await ref
                        .read(messagingControllerProvider.notifier)
                        .loadConversations();
                  } catch (_) {}
                },
                child: const Text('Create'),
              ),
            ],
          );
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final messagingState = ref.watch(messagingControllerProvider);
    final isDark = Theme.of(context).brightness == Brightness.dark;

    return Scaffold(
      drawer: const AppDrawer(currentRoute: '/messaging'),
      appBar: AppBar(
        // Show back arrow when push-navigated; drawer icon otherwise
        leading: Navigator.of(context).canPop()
            ? IconButton(
                icon: const Icon(Icons.arrow_back_rounded),
                tooltip: 'Go Back',
                onPressed: () => Navigator.of(context).pop(),
              )
            : null,
        title: const Text('Ops Communications'),
        bottom: TabBar(
          controller: _tabController,
          indicatorColor: NpontuColors.gold,
          indicatorWeight: 3,
          labelColor: Colors.white,
          unselectedLabelColor: Colors.white70,
          tabs: const [
            Tab(text: 'Channels', icon: Icon(Icons.tag_rounded, size: 18)),
            Tab(
              text: 'War Rooms',
              icon: Icon(Icons.flash_on_rounded, size: 18),
            ),
            Tab(
              text: 'Direct',
              icon: Icon(Icons.chat_bubble_outline_rounded, size: 18),
            ),
          ],
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh_rounded),
            tooltip: 'Refresh',
            onPressed: () => ref
                .read(messagingControllerProvider.notifier)
                .loadConversations(),
          ),
        ],
      ),
      floatingActionButton: FloatingActionButton(
        backgroundColor: NpontuColors.green,
        foregroundColor: Colors.white,
        tooltip: 'New Channel / War Room',
        onPressed: _openCreateConversationDialog,
        child: const Icon(Icons.add_comment_rounded),
      ),
      body: TabBarView(
        controller: _tabController,
        children: [
          _buildConversationList(
            messagingState,
            messagingState.conversations
                .where((c) => c.type == 'channel')
                .toList(),
            'No Public Channels',
            'No team ops channels found.',
            isDark,
          ),
          _buildConversationList(
            messagingState,
            messagingState.conversations
                .where((c) => c.type == 'war_room')
                .toList(),
            'No Active War Rooms',
            'All operational incidents are currently under control.',
            isDark,
          ),
          _buildConversationList(
            messagingState,
            messagingState.conversations
                .where((c) => c.type == 'direct')
                .toList(),
            'No Direct Messages',
            'Initiate a private operations chat with an SRE teammate.',
            isDark,
          ),
        ],
      ),
    );
  }

  Widget _buildConversationList(
    MessagingState state,
    List<ConversationModel> list,
    String emptyTitle,
    String emptySubtitle,
    bool isDark,
  ) {
    if (state.isLoading) {
      return const SkeletonListPlaceholder(count: 4);
    }

    if (state.errorMessage != null) {
      return ErrorRetryWidget(
        message: state.errorMessage!,
        onRetry: () =>
            ref.read(messagingControllerProvider.notifier).loadConversations(),
      );
    }

    if (list.isEmpty) {
      return EmptyStateWidget(
        icon: Icons.forum_outlined,
        title: emptyTitle,
        subtitle: emptySubtitle,
      );
    }

    return ListView.separated(
      padding: const EdgeInsets.symmetric(vertical: 8),
      itemCount: list.length,
      separatorBuilder: (_, __) => const Divider(height: 1, indent: 72),
      itemBuilder: (ctx, index) {
        final conv = list[index];
        return _buildConversationTile(ctx, conv, isDark);
      },
    );
  }

  Widget _buildConversationTile(
    BuildContext context,
    ConversationModel conv,
    bool isDark,
  ) {
    IconData iconData;
    Color iconColor;

    if (conv.type == 'war_room') {
      iconData = Icons.crisis_alert_rounded;
      iconColor = NpontuColors.danger;
    } else if (conv.type == 'channel') {
      iconData = Icons.tag_rounded;
      iconColor = NpontuColors.green;
    } else {
      iconData = Icons.person_rounded;
      iconColor = NpontuColors.gold;
    }

    return ListTile(
      leading: CircleAvatar(
        backgroundColor: iconColor.withAlpha(30),
        child: Icon(iconData, color: iconColor, size: 20),
      ),
      title: Row(
        children: [
          Expanded(
            child: Text(
              conv.title.isNotEmpty ? conv.title : 'Channel #${conv.id}',
              style: const TextStyle(fontWeight: FontWeight.w700, fontSize: 15),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ),
          if (conv.lastMessageAt != null)
            Text(
              conv.lastMessageAt!,
              style: const TextStyle(fontSize: 11, color: Colors.grey),
            ),
        ],
      ),
      subtitle: Row(
        children: [
          Expanded(
            child: Text(
              conv.lastMessage?.body ?? 'No messages yet',
              style: TextStyle(
                fontSize: 13,
                color: isDark
                    ? NpontuColors.textSecondaryDark
                    : NpontuColors.textSecondaryLight,
              ),
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
            ),
          ),
          if (conv.unreadCount > 0) ...[
            const SizedBox(width: 8),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 7, vertical: 2),
              decoration: BoxDecoration(
                color: NpontuColors.gold,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Text(
                '${conv.unreadCount}',
                style: const TextStyle(
                  fontSize: 11,
                  fontWeight: FontWeight.w800,
                  color: Color(0xFF1F2937),
                ),
              ),
            ),
          ],
        ],
      ),
      onTap: () => context.push('/messaging/${conv.id}'),
    );
  }
}
