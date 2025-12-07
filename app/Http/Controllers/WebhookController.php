<?php

namespace App\Http\Controllers;

use App\Models\Webhook;
use App\Services\SshKeyService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class WebhookController extends Controller
{
    public function handle(Request $request, $id, $token)
    {
        // 1. You should implement the signature verification logic here using $token 
        //    and the X-Hub-Signature-256 header.
        //    For now, we'll just log to confirm the route is working.

        Log::info('Webhook received successfully!', [
            'id' => $id,
            'token' => $token,
            'event' => $request->header('X-GitHub-Event'), // e.g., 'ping' or 'push'
        ]);
        
        // 2. Add your deployment logic here (e.g., dispatch a deployment Job)

        // Must return a 200 OK response to GitHub
        return response('Webhook Handled', 200);
    }
    public function __construct(
        protected SshKeyService $sshKeyService
    ) {
    }

    /**
     * Display a listing of the webhooks.
     */
    public function index()
    {
        $webhooks = Webhook::withCount('deployments')
            ->with('latestDeployment')
            ->latest()
            ->paginate(15);

        return view('webhooks.index', compact('webhooks'));
    }

    /**
     * Show the form for creating a new webhook.
     */
    public function create()
    {
        return view('webhooks.create');
    }

    /**
     * Store a newly created webhook in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['nullable', 'string', 'max:255'],
            'git_provider' => ['required', Rule::in(['github', 'gitlab'])],
            'repository_url' => ['required', 'string'],
            'branch' => ['required', 'string', 'max:255'],
            'local_path' => ['required', 'string', 'max:500'],
            'deploy_user' => ['nullable', 'string', 'max:255', 'regex:/^[a-z_][a-z0-9_-]*$/'],
            'is_active' => ['nullable', 'boolean'],
            'pre_deploy_script' => ['nullable', 'string'],
            'post_deploy_script' => ['nullable', 'string'],
            'generate_ssh_key' => ['nullable', 'boolean'],
        ]);

        $validated['secret_token'] = Str::random(64);
        $validated['is_active'] = $request->boolean('is_active');

        $webhook = Webhook::create($validated);

        // Generate SSH key if requested
        if ($request->boolean('generate_ssh_key')) {
            $this->sshKeyService->generateKeyPair($webhook);
        }

        return redirect()
            ->route('webhooks.show', $webhook)
            ->with('success', 'Webhook created successfully!');
    }

    /**
     * Display the specified webhook.
     */
    public function show(Webhook $webhook)
    {
        $webhook->load(['sshKey', 'deployments' => function ($query) {
            $query->latest()->take(20);
        }]);

        return view('webhooks.show', compact('webhook'));
    }

    /**
     * Show the form for editing the specified webhook.
     */
    public function edit(Webhook $webhook)
    {
        return view('webhooks.edit', compact('webhook'));
    }

    /**
     * Update the specified webhook in storage.
     */
    public function update(Request $request, Webhook $webhook)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'domain' => ['nullable', 'string', 'max:255'],
            'git_provider' => ['required', Rule::in(['github', 'gitlab'])],
            'repository_url' => ['required', 'string'],
            'branch' => ['required', 'string', 'max:255'],
            'local_path' => ['required', 'string', 'max:500'],
            'deploy_user' => ['nullable', 'string', 'max:255', 'regex:/^[a-z_][a-z0-9_-]*$/'],
            'is_active' => ['nullable', 'boolean'],
            'pre_deploy_script' => ['nullable', 'string'],
            'post_deploy_script' => ['nullable', 'string'],
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        $webhook->update($validated);

        return redirect()
            ->route('webhooks.show', $webhook)
            ->with('success', 'Webhook updated successfully!');
    }

    /**
     * Remove the specified webhook from storage.
     */
    public function destroy(Webhook $webhook)
    {
        $webhook->delete();

        return redirect()
            ->route('webhooks.index')
            ->with('success', 'Webhook deleted successfully!');
    }

    /**
     * Generate or regenerate SSH key for webhook.
     */
    public function generateSshKey(Webhook $webhook)
    {
        $this->sshKeyService->generateKeyPair($webhook);

        return redirect()
            ->route('webhooks.show', $webhook)
            ->with('success', 'SSH key generated successfully!');
    }

    /**
     * Toggle webhook active status.
     */
    public function toggle(Webhook $webhook)
    {
        $webhook->update(['is_active' => !$webhook->is_active]);

        $status = $webhook->is_active ? 'activated' : 'deactivated';

        return redirect()
            ->back()
            ->with('success', "Webhook {$status} successfully!");
    }
}
