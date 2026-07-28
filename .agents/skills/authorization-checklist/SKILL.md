---
name: authorization-checklist
description: >
  Policy + Form Request pairing pattern for the Support Activity Tracker.
  Use this skill whenever adding a new controller action to ensure consistent
  authorization and validation discipline.
---

# Authorization Checklist Skill

Every controller action in this project must use **both** a Form Request (validation)
and a Policy (authorization). This skill documents the pattern.

---

## The Pairing Rule

| Concern | Tool | Location |
|---|---|---|
| **Input validation** | Form Request | app/Http/Requests/ |
| **Access control** | Policy method | app/Policies/ |
| **Business logic** | Action class | app/Actions/ |
| **HTTP response** | Controller | app/Http/Controllers/ |

A controller method that lacks any of these four is incomplete.

---

## 1. Registering Policies

In pp/Providers/AuthServiceProvider.php:

`php
protected \ = [
    Activity::class => ActivityPolicy::class,
    User::class     => UserPolicy::class,
];
`

---

## 2. Form Request Template

`php
<?php
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreActivityRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization via Policy -- return true here, Gate checks happen in the Policy
        return \->user()->can('create', Activity::class);
    }

    public function rules(): array
    {
        return [
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:2000'],
            'date'        => ['required', 'date', 'date_format:Y-m-d'],
            'category'    => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'An activity title is required.',
            'date.required'  => 'Please specify the activity date.',
        ];
    }
}
`

**Rules**:
- Always define messages() with user-facing copy (no raw Laravel defaults in the UI).
- Always use array syntax for rules (not pipe-separated strings) for readability.
- uthorize() in the Form Request is the canonical place to call Policy checks.

---

## 3. Policy Template

`php
<?php
declare(strict_types=1);

namespace App\Policies;

use App\Models\Activity;
use App\Models\User;

class ActivityPolicy
{
    /**
     * Any authenticated user can view the activity list.
     */
    public function viewAny(User \): bool
    {
        return true;
    }

    /**
     * Any authenticated user can view a specific activity.
     */
    public function view(User \, Activity \): bool
    {
        return true;
    }

    /**
     * Only support or admin users can create activities.
     */
    public function create(User \): bool
    {
        return in_array(\->role, ['admin', 'support'], strict: true);
    }

    /**
     * Any authenticated user can update any activity (shift handover context).
     */
    public function update(User \, Activity \): bool
    {
        return in_array(\->role, ['admin', 'support'], strict: true);
    }

    /**
     * Only admins can delete activities.
     */
    public function delete(User \, Activity \): bool
    {
        return \->role === 'admin';
    }

    /**
     * Only admins can restore soft-deleted activities.
     */
    public function restore(User \, Activity \): bool
    {
        return \->role === 'admin';
    }
}
`

---

## 4. Controller Integration

`php
<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Actions\Activities\CreateActivityAction;
use App\Http\Requests\StoreActivityRequest;
use App\Models\Activity;

class ActivityController extends Controller
{
    public function store(StoreActivityRequest \, CreateActivityAction \)
    {
        // Authorization already resolved by StoreActivityRequest::authorize()
        // (which calls the Policy). No separate authorize() call needed here.

        \ = \->execute(\->validated());

        return redirect()
            ->route('activities.show', \)
            ->with('success', 'Activity created.');
    }
}
`

**Note**: When authorization is handled in FormRequest::authorize(), the controller does not
need a duplicate \->authorize() call. But if you use a plain Request instead of a
Form Request, you MUST call \->authorize() explicitly in the controller.

---

## 5. Pre-Commit Checklist

Before committing any controller action, verify:

- [ ] A Form Request exists and is type-hinted in the method signature.
- [ ] uthorize() in the Form Request calls the correct Policy method.
- [ ] The Policy method is defined and registered in AuthServiceProvider.
- [ ] The controller delegates business logic to an Action class.
- [ ] No validation logic exists in the controller body.
- [ ] No if (\->role === ...) checks exist in the controller body.

---

## 6. Role Reference

| Role | Slug | Capabilities |
|---|---|---|
| Administrator | admin | Full access: manage users, CRUD activities, delete, view reports, view audit logs |
| Support | support | Create and update activities, view daily board, view reports |

No other roles exist. Any code checking for a third role is a bug.
