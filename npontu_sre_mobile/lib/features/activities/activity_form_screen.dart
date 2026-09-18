// lib/features/activities/activity_form_screen.dart

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';

import '../../core/theme/npontu_theme.dart';
import '../../shared/models/activity_model.dart';
import '../team/team_controller.dart';
import 'activities_controller.dart';

class ActivityFormScreen extends ConsumerStatefulWidget {
  final int? activityId;

  const ActivityFormScreen({super.key, this.activityId});

  @override
  ConsumerState<ActivityFormScreen> createState() => _ActivityFormScreenState();
}

class _ActivityFormScreenState extends ConsumerState<ActivityFormScreen> {
  final _formKey = GlobalKey<FormState>();

  late TextEditingController _titleController;
  late TextEditingController _descController;
  late TextEditingController _slaTimeController;

  String _shift = 'morning';
  String _priority = 'medium';
  String _recurrence = 'daily';
  int? _assignedToId;
  bool _isPinned = false;
  bool _isActive = true;
  bool _isSubmitting = false;
  bool _isLoadingData = false;

  @override
  void initState() {
    super.initState();
    _titleController = TextEditingController();
    _descController = TextEditingController();
    _slaTimeController = TextEditingController();

    WidgetsBinding.instance.addPostFrameCallback((_) async {
      ref.read(teamControllerProvider.notifier).loadTeam();

      if (widget.activityId != null) {
        setState(() => _isLoadingData = true);
        final activities = ref.read(activitiesControllerProvider).activities;
        var existing = activities.cast<ActivityModel?>().firstWhere(
          (a) => a?.id == widget.activityId,
          orElse: () => null,
        );

        existing ??= await ref
            .read(activitiesControllerProvider.notifier)
            .fetchActivity(widget.activityId!);

        if (existing != null && mounted) {
          setState(() {
            _titleController.text = existing!.title;
            _descController.text = existing.description ?? '';
            _slaTimeController.text = existing.slaTime ?? '';
            _shift = existing.shift;
            _priority = existing.priority;
            _recurrence = existing.recurrence;
            _assignedToId = existing.assignedTo;
            _isPinned = existing.isPinned;
            _isActive = existing.isActive;
            _isLoadingData = false;
          });
        } else if (mounted) {
          setState(() => _isLoadingData = false);
        }
      }
    });
  }

  @override
  void dispose() {
    _titleController.dispose();
    _descController.dispose();
    _slaTimeController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isSubmitting = true);

    final payload = <String, dynamic>{
      'title': _titleController.text.trim(),
      'description': _descController.text.trim().isEmpty
          ? null
          : _descController.text.trim(),
      'shift': _shift,
      'priority': _priority,
      'recurrence': _recurrence,
      'sla_time': _slaTimeController.text.trim().isEmpty
          ? null
          : _slaTimeController.text.trim(),
      'assigned_to': _assignedToId,
      'is_pinned': _isPinned,
      'is_active': _isActive,
    };

    bool success;
    if (widget.activityId != null) {
      success = await ref
          .read(activitiesControllerProvider.notifier)
          .updateActivity(widget.activityId!, payload);
    } else {
      success = await ref
          .read(activitiesControllerProvider.notifier)
          .createActivity(payload);
    }

    setState(() => _isSubmitting = false);

    if (mounted) {
      if (success) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(
              widget.activityId != null
                  ? 'Activity updated.'
                  : 'Activity created.',
            ),
            backgroundColor: NpontuColors.green,
          ),
        );
        context.pop();
      } else {
        final errorMsg =
            ref.read(activitiesControllerProvider).errorMessage ??
            'Failed to save activity.';
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(
            content: Text(errorMsg),
            backgroundColor: NpontuColors.danger,
          ),
        );
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final isEdit = widget.activityId != null;
    final teamMembers = ref.watch(teamControllerProvider).members;

    return Scaffold(
      appBar: AppBar(
        title: Text(isEdit ? 'Edit Activity' : 'New Activity'),
      ),
      body: _isLoadingData
          ? const Center(
              child: CircularProgressIndicator(color: NpontuColors.green),
            )
          : Form(
              key: _formKey,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  TextFormField(
                    controller: _titleController,
                    decoration: const InputDecoration(
                      labelText: 'Activity Title *',
                      hintText: 'e.g., Verify DB replication latency',
                      prefixIcon: Icon(Icons.title_rounded),
                    ),
                    validator: (val) => (val == null || val.trim().isEmpty)
                        ? 'Please enter a title'
                        : null,
                  ),
                  const SizedBox(height: 16),
                  TextFormField(
                    controller: _descController,
                    decoration: const InputDecoration(
                      labelText: 'Description / Runbook Link',
                      hintText: 'Steps, command snippet, or SOP URL...',
                      prefixIcon: Icon(Icons.description_outlined),
                    ),
                    maxLines: 3,
                  ),
                  const SizedBox(height: 16),

                  // Shift & Priority Row
                  Row(
                    children: [
                      Expanded(
                        child: DropdownButtonFormField<String>(
                          value: _shift,
                          isExpanded: true,
                          decoration: const InputDecoration(
                            labelText: 'Shift *',
                          ),
                          items: const [
                            DropdownMenuItem(
                              value: 'morning',
                              child: Text('Morning'),
                            ),
                            DropdownMenuItem(
                              value: 'afternoon',
                              child: Text('Afternoon'),
                            ),
                            DropdownMenuItem(
                              value: 'night',
                              child: Text('Night'),
                            ),
                          ],
                          onChanged: (val) {
                            if (val != null) setState(() => _shift = val);
                          },
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: DropdownButtonFormField<String>(
                          value: _priority,
                          isExpanded: true,
                          decoration: const InputDecoration(
                            labelText: 'Priority *',
                          ),
                          items: const [
                            DropdownMenuItem(value: 'low', child: Text('Low')),
                            DropdownMenuItem(
                              value: 'medium',
                              child: Text('Medium'),
                            ),
                            DropdownMenuItem(
                              value: 'high',
                              child: Text('High'),
                            ),
                            DropdownMenuItem(
                              value: 'critical',
                              child: Text('Critical'),
                            ),
                          ],
                          onChanged: (val) {
                            if (val != null) setState(() => _priority = val);
                          },
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),

                  // SLA Time & Recurrence Row
                  Row(
                    children: [
                      Expanded(
                        child: TextFormField(
                          controller: _slaTimeController,
                          decoration: const InputDecoration(
                            labelText: 'SLA Window',
                            hintText: '09:00:00',
                            prefixIcon: Icon(Icons.alarm_rounded),
                          ),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: DropdownButtonFormField<String>(
                          value: _recurrence,
                          isExpanded: true,
                          decoration: const InputDecoration(
                            labelText: 'Cadence *',
                          ),
                          items: const [
                            DropdownMenuItem(
                              value: 'daily',
                              child: Text('Daily'),
                            ),
                            DropdownMenuItem(
                              value: 'weekdays',
                              child: Text('Weekdays'),
                            ),
                            DropdownMenuItem(
                              value: 'weekly',
                              child: Text('Weekly'),
                            ),
                            DropdownMenuItem(
                              value: 'monthly',
                              child: Text('Monthly'),
                            ),
                          ],
                          onChanged: (val) {
                            if (val != null) setState(() => _recurrence = val);
                          },
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 16),

                   // Assignee
                  DropdownButtonFormField<int?>(
                    value: _assignedToId,
                    isExpanded: true,
                    decoration: const InputDecoration(
                      labelText: 'Assigned Operator',
                      prefixIcon: Icon(Icons.person_outline_rounded),
                    ),
                    selectedItemBuilder: (context) {
                      // Build the list of selected-display widgets in the same
                      // order as [items] below: null first, then conditional
                      // current-assignee, then team members.
                      // Wrap each in Flexible so the InputDecorator row never
                      // overflows on narrow screens.
                      Widget chip(String label) => Flexible(
                            child: Text(
                              label,
                              overflow: TextOverflow.ellipsis,
                              maxLines: 1,
                            ),
                          );
                      final displayItems = <Widget>[
                        chip('Unassigned (Shift Pool)'),
                        if (_assignedToId != null &&
                            teamMembers.every((u) => u.id != _assignedToId))
                          chip('Current Assignee (ID: $_assignedToId)'),
                        ...teamMembers.map(
                          (user) => chip('${user.name} (${user.gradeLabel})'),
                        ),
                      ];
                      return displayItems;
                    },
                    items: [
                      const DropdownMenuItem<int?>(
                        value: null,
                        child: Text(
                          'Unassigned (Shift Pool)',
                          overflow: TextOverflow.ellipsis,
                          maxLines: 1,
                        ),
                      ),
                      if (_assignedToId != null &&
                          teamMembers.every((u) => u.id != _assignedToId))
                        DropdownMenuItem<int?>(
                          value: _assignedToId,
                          child: Text(
                            'Current Assignee (ID: $_assignedToId)',
                            overflow: TextOverflow.ellipsis,
                            maxLines: 1,
                          ),
                        ),
                      ...teamMembers.map(
                        (user) => DropdownMenuItem<int?>(
                          value: user.id,
                          child: Text(
                            '${user.name} (${user.gradeLabel})',
                            overflow: TextOverflow.ellipsis,
                            maxLines: 1,
                          ),
                        ),
                      ),
                    ],
                    onChanged: (val) => setState(() => _assignedToId = val),
                  ),
                  const SizedBox(height: 16),

                  // Switches
                  SwitchListTile(
                    contentPadding: EdgeInsets.zero,
                    title: const Text('Pin to top of shift view'),
                    subtitle: const Text(
                      'Highlights critical handover items',
                      overflow: TextOverflow.ellipsis,
                      maxLines: 2,
                    ),
                    value: _isPinned,
                    activeColor: NpontuColors.gold,
                    onChanged: (val) => setState(() => _isPinned = val),
                  ),
                  SwitchListTile(
                    contentPadding: EdgeInsets.zero,
                    title: const Text('Active status'),
                    subtitle: const Text(
                      'Inactive items will not appear in daily checklist',
                      overflow: TextOverflow.ellipsis,
                      maxLines: 2,
                    ),
                    value: _isActive,
                    activeColor: NpontuColors.green,
                    onChanged: (val) => setState(() => _isActive = val),
                  ),
                  const SizedBox(height: 24),

                  ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: NpontuColors.green,
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10),
                      ),
                    ),
                    onPressed: _isSubmitting ? null : _submit,
                    child: _isSubmitting
                        ? const SizedBox(
                            height: 20,
                            width: 20,
                            child: CircularProgressIndicator(
                              strokeWidth: 2,
                              color: Colors.white,
                            ),
                          )
                        : Text(
                            isEdit
                                ? 'Save Activity Changes'
                                : 'Create Operational Activity',
                            style: const TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                  ),
                ],
              ),
            ),
    );
  }
}
