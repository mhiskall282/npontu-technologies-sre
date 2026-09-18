// lib/features/handovers/handover_form_screen.dart

import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:intl/intl.dart';

import '../../core/theme/npontu_theme.dart';
import '../team/team_controller.dart';
import 'handovers_controller.dart';

class HandoverFormScreen extends ConsumerStatefulWidget {
  const HandoverFormScreen({super.key});

  @override
  ConsumerState<HandoverFormScreen> createState() => _HandoverFormScreenState();
}

class _HandoverFormScreenState extends ConsumerState<HandoverFormScreen> {
  final _formKey = GlobalKey<FormState>();

  String _shift = 'morning';
  late DateTime _selectedDate;
  int? _incomingUserId;
  final _summaryController = TextEditingController();
  final _outstandingController = TextEditingController();
  bool _isSubmitting = false;

  @override
  void initState() {
    super.initState();
    _selectedDate = DateTime.now();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(teamControllerProvider.notifier).loadTeam();
    });
  }

  @override
  void dispose() {
    _summaryController.dispose();
    _outstandingController.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isSubmitting = true);

    final payload = <String, dynamic>{
      'shift': _shift,
      'handover_date': DateFormat('yyyy-MM-dd').format(_selectedDate),
      'incoming_user_id': _incomingUserId,
      'summary': _summaryController.text.trim().isEmpty
          ? null
          : _summaryController.text.trim(),
      'outstanding_items': _outstandingController.text.trim().isEmpty
          ? null
          : _outstandingController.text.trim(),
    };

    final success = await ref
        .read(handoversControllerProvider.notifier)
        .createHandover(payload);

    setState(() => _isSubmitting = false);

    if (mounted) {
      if (success) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text(
              'Shift handover briefing created and logged to audit trail.',
            ),
            backgroundColor: NpontuColors.green,
          ),
        );
        context.pop();
      } else {
        final errorMsg =
            ref.read(handoversControllerProvider).errorMessage ??
            'Failed to initiate handover.';
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
    final teamMembers = ref.watch(teamControllerProvider).members;

    return Scaffold(
      appBar: AppBar(title: const Text('New Shift Handover')),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            // Shift selector
            DropdownButtonFormField<String>(
              value: _shift,
              isExpanded: true,
              decoration: const InputDecoration(
                labelText: 'Ending Shift *',
                prefixIcon: Icon(Icons.access_time_rounded),
              ),
              items: const [
                DropdownMenuItem(
                  value: 'morning',
                  child: Text('Morning Shift'),
                ),
                DropdownMenuItem(
                  value: 'afternoon',
                  child: Text('Afternoon Shift'),
                ),
                DropdownMenuItem(value: 'night', child: Text('Night Shift')),
              ],
              onChanged: (val) {
                if (val != null) setState(() => _shift = val);
              },
            ),
            const SizedBox(height: 16),

            // Date picker
            ListTile(
              contentPadding: const EdgeInsets.symmetric(
                horizontal: 12,
                vertical: 4,
              ),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(8),
                side: BorderSide(color: Colors.grey.withAlpha(80)),
              ),
              leading: const Icon(
                Icons.calendar_today_rounded,
                color: NpontuColors.green,
              ),
              title: const Text(
                'Handover Date',
                style: TextStyle(fontSize: 12, color: Colors.grey),
              ),
              subtitle: Text(
                DateFormat('EEE, MMM d, yyyy').format(_selectedDate),
                style: const TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.w700,
                ),
              ),
              trailing: const Icon(Icons.edit_calendar_rounded, size: 20),
              onTap: () async {
                final picked = await showDatePicker(
                  context: context,
                  initialDate: _selectedDate,
                  firstDate: DateTime.now().subtract(const Duration(days: 30)),
                  lastDate: DateTime.now().add(const Duration(days: 7)),
                );
                if (picked != null) setState(() => _selectedDate = picked);
              },
            ),
            const SizedBox(height: 16),

            // Incoming Operator
            DropdownButtonFormField<int?>(
              value: _incomingUserId,
              isExpanded: true,
              decoration: const InputDecoration(
                labelText: 'Incoming SRE Operator (Receiver)',
                prefixIcon: Icon(Icons.person_add_alt_1_rounded),
              ),
              items: [
                const DropdownMenuItem<int?>(
                  value: null,
                  child: Text(
                    'Open / Next Available Operator',
                    overflow: TextOverflow.ellipsis,
                  ),
                ),
                ...teamMembers.map(
                  (user) => DropdownMenuItem<int?>(
                    value: user.id,
                    child: Text(
                      '${user.name} (${user.gradeLabel})',
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ),
              ],
              onChanged: (val) => setState(() => _incomingUserId = val),
            ),
            const SizedBox(height: 16),

            // Shift Summary
            TextFormField(
              controller: _summaryController,
              decoration: const InputDecoration(
                labelText: 'Shift Operational Summary *',
                hintText: 'Summary of critical deployments, incidents handled, cluster status...',
                prefixIcon: Icon(Icons.summarize_rounded),
              ),
              maxLines: 4,
              validator: (val) => (val == null || val.trim().isEmpty)
                  ? 'Please enter a summary of the shift'
                  : null,
            ),
            const SizedBox(height: 16),

            // Outstanding Items
            TextFormField(
              controller: _outstandingController,
              decoration: const InputDecoration(
                labelText: 'Outstanding Incidents / In-Progress Tasks',
                hintText: 'Items requiring follow-up by the incoming shift...',
                prefixIcon: Icon(Icons.warning_amber_rounded),
              ),
              maxLines: 3,
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
                  : const Text(
                      'Submit Handover Briefing',
                      style: TextStyle(
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
