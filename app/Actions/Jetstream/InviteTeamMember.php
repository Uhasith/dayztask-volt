<?php

namespace App\Actions\Jetstream;

use App\Mail\NewTeamInvite;
use App\Mail\OldTeamInvite;
use App\Models\Team;
use App\Models\User;
use Closure;
use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Laravel\Jetstream\Events\InvitingTeamMember;
use Laravel\Jetstream\Jetstream;
use Laravel\Jetstream\Rules\Role;

class InviteTeamMember
{
    /**
     * Invite a new team member to the given team.
     */
    public function invite(User $user, Team $team, string $email, ?string $role = null): void
    {
        Gate::forUser($user)->authorize('addTeamMember', $team);

        $this->validate($team, $email, $role);

        InvitingTeamMember::dispatch($team, $email, $role);

        $existingUser = User::where('email', $email)->first();

        if ($existingUser) {
            // Check if the user is already part of the team
            if (! $existingUser->belongsToTeam($team)) {
                // Attach user to the team with the specified role
                $team->users()->attach($existingUser->id, ['role' => $role]);

                $existingUser->current_team_id = $team->id;
                $existingUser->current_workspace_id = $team->workspaces()->first()->id;
                $existingUser->save();

                $mailData = [
                    'email' => $email,
                    'team' => $team,
                    'user' => $existingUser,
                    'username' => $existingUser->name,
                    'loginUrl' => env('APP_URL').'/auth/login',
                    'email_subject' => __('You have been invited to join the :team team!', ['team' => $team->name]),
                ];

                Mail::to($mailData['email'])->queue(new OldTeamInvite($mailData));
            }
        } else {
            // Create a new user with a default password
            $password = Str::random(10);  // Generate a random password

            $newUser = User::create([
                'name' => 'New User '.substr($email, 0, strpos($email, '@')),  // Handle name as required
                'email' => $email,
                'password' => Hash::make($password),
                'current_team_id' => $team->id,
                'current_workspace_id' => $team->workspaces()->first()->id,
            ]);

            // Attach the new user to the team
            $team->users()->attach($newUser->id, ['role' => $role]);

            $mailData = [
                'email' => $email,
                'password' => $password,
                'team' => $team,
                'user' => $newUser,
                'username' => $newUser->name,
                'loginUrl' => env('APP_URL').'/auth/login',
                'email_subject' => __('You have been invited to join the :team team!', ['team' => $team->name]),
            ];

            Mail::to($mailData['email'])->queue(new NewTeamInvite($mailData));

        }
    }

    /**
     * Validate the invite member operation.
     */
    protected function validate(Team $team, string $email, ?string $role): void
    {
        Validator::make([
            'email' => $email,
            'role' => $role,
        ], $this->rules($team), [
            'email.unique' => __('This user has already been invited to the team.'),
        ])->after(
            $this->ensureUserIsNotAlreadyOnTeam($team, $email)
        )->validateWithBag('addTeamMember');
    }

    /**
     * Get the validation rules for inviting a team member.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    protected function rules(Team $team): array
    {
        return array_filter([
            'email' => [
                'required',
                'email',
                Rule::unique('team_invitations')->where(function (Builder $query) use ($team) {
                    $query->where('team_id', $team->id);
                }),
            ],
            'role' => Jetstream::hasRoles()
                ? ['required', 'string', new Role]
                : null,
        ]);
    }

    /**
     * Ensure that the user is not already on the team.
     */
    protected function ensureUserIsNotAlreadyOnTeam(Team $team, string $email): Closure
    {
        return function ($validator) use ($team, $email) {
            $validator->errors()->addIf(
                $team->hasUserWithEmail($email),
                'email',
                __('This user already belongs to the team.')
            );
        };
    }
}
