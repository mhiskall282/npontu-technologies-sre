// lib/features/workspaces/workspace_switcher_sheet.dart

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';

import '../../core/theme/npontu_theme.dart';
import 'workspace_controller.dart';

class WorkspaceSwitcherSheet extends ConsumerStatefulWidget {
  const WorkspaceSwitcherSheet({super.key});

  static Future<void> show(BuildContext context) {
    return showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => const WorkspaceSwitcherSheet(),
    );
  }

  @override
  ConsumerState<WorkspaceSwitcherSheet> createState() =>
      _WorkspaceSwitcherSheetState();
}

class _WorkspaceSwitcherSheetState
    extends ConsumerState<WorkspaceSwitcherSheet> {
  final _codeController = TextEditingController();
  bool _isJoining = false;
  String? _errorMessage;

  @override
  void dispose() {
    _codeController.dispose();
    super.dispose();
  }

  Future<void> _handleJoin() async {
    final code = _codeController.text.trim();
    if (code.isEmpty) return;

    setState(() {
      _isJoining = true;
      _errorMessage = null;
    });

    try {
      final joined = await ref
          .read(workspacesControllerProvider.notifier)
          .joinByCompanyCode(code);

      if (mounted) {
        if (joined != null) {
          Navigator.of(context).pop();
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              content: Text('Joined and switched to ${joined.name}'),
              backgroundColor: OpsoraColors.green,
            ),
          );
        } else {
          setState(() {
            _errorMessage = 'Could not join organization with this code.';
          });
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _errorMessage = e.toString().replaceAll('Exception:', '').trim();
        });
      }
    } finally {
      if (mounted) {
        setState(() {
          _isJoining = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final workspacesAsync = ref.watch(workspacesControllerProvider);
    final activeId = ref.watch(activeWorkspaceIdProvider);

    return Container(
      decoration: const BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      padding: EdgeInsets.only(
        top: 20,
        left: 20,
        right: 20,
        bottom: MediaQuery.of(context).viewInsets.bottom + 24,
      ),
      child: Column(
        mainAxisSize: MainAxisSize.min,
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Drag handle
          Center(
            child: Container(
              width: 40,
              height: 4,
              decoration: BoxDecoration(
                color: Colors.grey.shade300,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
          ),
          const SizedBox(height: 16),

          // Header
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                'Switch Workspace',
                style: TextStyle(
                  fontSize: 18,
                  fontWeight: FontWeight.bold,
                  letterSpacing: -0.3,
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                decoration: BoxDecoration(
                  color: OpsoraColors.green.withAlpha(25),
                  borderRadius: BorderRadius.circular(6),
                ),
                child: const Text(
                  'Opsora Multi-Tenant',
                  style: TextStyle(
                    fontSize: 10,
                    fontWeight: FontWeight.w700,
                    color: OpsoraColors.green,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 12),

          // Workspace List
          workspacesAsync.when(
            loading: () => const Center(
              child: Padding(
                padding: EdgeInsets.all(24.0),
                child: CircularProgressIndicator(color: OpsoraColors.green),
              ),
            ),
            error: (err, _) => Padding(
              padding: const EdgeInsets.all(16.0),
              child: Text(
                'Failed to load workspaces: $err',
                style: const TextStyle(color: OpsoraColors.danger, fontSize: 13),
              ),
            ),
            data: (workspaces) {
              if (workspaces.isEmpty) {
                return const Padding(
                  padding: EdgeInsets.all(16.0),
                  child: Text(
                    'No workspaces found for your account.',
                    style: TextStyle(color: Colors.grey, fontSize: 13),
                  ),
                );
              }

              return Column(
                children: workspaces.map((ws) {
                  final isSelected = activeId == ws.id.toString();

                  return ListTile(
                    contentPadding: const EdgeInsets.symmetric(
                      horizontal: 8,
                      vertical: 2,
                    ),
                    leading: CircleAvatar(
                      backgroundColor: ws.isPersonal
                          ? Colors.indigo.shade50
                          : OpsoraColors.green.withAlpha(25),
                      child: Icon(
                        ws.isPersonal
                            ? Icons.person_outline
                            : Icons.business_outlined,
                        color: ws.isPersonal
                            ? Colors.indigo
                            : OpsoraColors.green,
                        size: 20,
                      ),
                    ),
                    title: Text(
                      ws.name,
                      style: TextStyle(
                        fontWeight:
                            isSelected ? FontWeight.bold : FontWeight.w600,
                        fontSize: 14,
                      ),
                    ),
                    subtitle: Text(
                      ws.isPersonal
                          ? 'Personal Workspace • Role: ${ws.myRole.toUpperCase()}'
                          : '${ws.organizationName ?? "Org"} • Role: ${ws.myRole.toUpperCase()}',
                      style: TextStyle(fontSize: 11, color: Colors.grey.shade600),
                    ),
                    trailing: isSelected
                        ? const Icon(
                            Icons.check_circle,
                            color: OpsoraColors.green,
                            size: 22,
                          )
                        : null,
                    onTap: () async {
                      await ref
                          .read(workspacesControllerProvider.notifier)
                          .switchWorkspace(ws);
                      if (context.mounted) {
                        Navigator.of(context).pop();
                      }
                    },
                  );
                }).toList(),
              );
            },
          ),

          const Divider(height: 24),

          // Join by Company Code
          const Text(
            'Join Another Organization',
            style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold),
          ),
          const SizedBox(height: 8),

          Row(
            children: [
              Expanded(
                child: TextField(
                  controller: _codeController,
                  textCapitalization: TextCapitalization.characters,
                  decoration: InputDecoration(
                    hintText: 'Enter Company Code (e.g. NPT-OPS-01)',
                    hintStyle: TextStyle(fontSize: 12, color: Colors.grey.shade400),
                    contentPadding: const EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 10,
                    ),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: BorderSide(color: Colors.grey.shade300),
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(8),
                      borderSide: const BorderSide(color: OpsoraColors.green),
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 8),
              ElevatedButton(
                onPressed: _isJoining ? null : _handleJoin,
                style: ElevatedButton.styleFrom(
                  backgroundColor: OpsoraColors.green,
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 12,
                  ),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(8),
                  ),
                ),
                child: _isJoining
                    ? const SizedBox(
                        width: 16,
                        height: 16,
                        child: CircularProgressIndicator(
                          color: Colors.white,
                          strokeWidth: 2,
                        ),
                      )
                    : const Text(
                        'Join',
                        style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13),
                      ),
              ),
            ],
          ),

          if (_errorMessage != null) ...[
            const SizedBox(height: 6),
            Text(
              _errorMessage!,
              style: const TextStyle(color: OpsoraColors.danger, fontSize: 11),
            ),
          ],
        ],
      ),
    );
  }
}
