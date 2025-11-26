@extends('layouts.app')

@section('title', 'Edit Webhook - Git Webhook Manager')
@section('page-title', 'Edit Webhook')
@section('page-description', 'Update webhook configuration')

@section('page-actions')
    <a href="{{ route('webhooks.show', $webhook) }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Back to Details
    </a>
@endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <form action="{{ route('webhooks.update', $webhook) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-info-circle me-2"></i> Basic Information
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Webhook Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $webhook->name) }}" required>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="domain" class="form-label">Domain / Website Reference</label>
                            <input type="text" class="form-control @error('domain') is-invalid @enderror" id="domain" name="domain" value="{{ old('domain', $webhook->domain) }}" placeholder="example.com">
                            @error('domain')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input type="hidden" name="is_active" value="0">
                            <input class="form-check-input" type="checkbox" id="is_active" name="is_active" value="1" {{ old('is_active', $webhook->is_active) ? 'checked' : '' }}>
                            <label class="form-check-label" for="is_active">
                                Active
                            </label>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-git me-2"></i> Repository Configuration
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="git_provider" class="form-label">Git Provider <span class="text-danger">*</span></label>
                            <select class="form-select @error('git_provider') is-invalid @enderror" id="git_provider" name="git_provider" required>
                                <option value="github" {{ old('git_provider', $webhook->git_provider) == 'github' ? 'selected' : '' }}>GitHub</option>
                                <option value="gitlab" {{ old('git_provider', $webhook->git_provider) == 'gitlab' ? 'selected' : '' }}>GitLab</option>
                            </select>
                            @error('git_provider')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="repository_url" class="form-label">Repository URL <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('repository_url') is-invalid @enderror" id="repository_url" name="repository_url" value="{{ old('repository_url', $webhook->repository_url) }}" required>
                            @error('repository_url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="branch" class="form-label">Branch <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('branch') is-invalid @enderror" id="branch" name="branch" value="{{ old('branch', $webhook->branch) }}" required>
                            @error('branch')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="local_path" class="form-label">Local Path <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('local_path') is-invalid @enderror" id="local_path" name="local_path" value="{{ old('local_path', $webhook->local_path) }}" required>
                            @error('local_path')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="deploy_user" class="form-label">Deploy User</label>
                            <input type="text" class="form-control @error('deploy_user') is-invalid @enderror" id="deploy_user" name="deploy_user" value="{{ old('deploy_user', $webhook->deploy_user ?? 'www-data') }}" placeholder="www-data">
                            <div class="form-text">
                                User that will execute deployment commands. Ensure this user has permissions for the deployment path.
                                <br>Common users: <code>www-data</code>, <code>www</code>, <code>nginx</code>, <code>apache</code>, <code>http</code>
                            </div>
                            @error('deploy_user')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <i class="bi bi-terminal me-2"></i> Deploy Scripts (Optional)
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="pre_deploy_script" class="form-label">Pre-Deploy Script</label>
                            <textarea class="form-control font-monospace @error('pre_deploy_script') is-invalid @enderror" id="pre_deploy_script" name="pre_deploy_script" rows="4">{{ old('pre_deploy_script', $webhook->pre_deploy_script) }}</textarea>
                            @error('pre_deploy_script')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="post_deploy_script" class="form-label">Post-Deploy Script</label>
                            <textarea class="form-control font-monospace @error('post_deploy_script') is-invalid @enderror" id="post_deploy_script" name="post_deploy_script" rows="4">{{ old('post_deploy_script', $webhook->post_deploy_script) }}</textarea>
                            @error('post_deploy_script')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i> Update Webhook
                    </button>
                    <a href="{{ route('webhooks.show', $webhook) }}" class="btn btn-outline-secondary">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
