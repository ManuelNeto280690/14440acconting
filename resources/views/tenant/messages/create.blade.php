@extends('layouts.tenant')

@section('title', 'Send Message')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-comment me-2"></i>Send Message
                        @if($client)
                            to {{ $client->name }}
                        @endif
                    </h3>
                    <div class="card-tools">
                        <a href="{{ $client ? route('tenant.clients.show', $client) : route('tenant.chat.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i>Back
                        </a>
                    </div>
                </div>
                
                <form action="{{ route('tenant.chat.send') }}" method="POST" id="messageForm">
                    @csrf
                    <div class="card-body">
                        @if($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="client_id" class="form-label">Client <span class="text-danger">*</span></label>
                                    <select name="client_id" id="client_id" class="form-select @error('client_id') is-invalid @enderror" required>
                                        <option value="">Select a client...</option>
                                        @foreach($clients as $clientOption)
                                            <option value="{{ $clientOption->id }}" 
                                                {{ ($client && $client->id === $clientOption->id) ? 'selected' : '' }}>
                                                {{ $clientOption->name }} ({{ $clientOption->email }})
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('client_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label for="type" class="form-label">Message Type</label>
                                    <select name="type" id="type" class="form-select @error('type') is-invalid @enderror">
                                        <option value="text" {{ old('type', 'text') === 'text' ? 'selected' : '' }}>Text</option>
                                        <option value="system" {{ old('type') === 'system' ? 'selected' : '' }}>System</option>
                                        <option value="notification" {{ old('type') === 'notification' ? 'selected' : '' }}>Notification</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-3">
                            <label for="message" class="form-label">Message <span class="text-danger">*</span></label>
                            <textarea name="message" id="message" rows="6" 
                                class="form-control @error('message') is-invalid @enderror" 
                                placeholder="Type your message here..." 
                                maxlength="2000" required>{{ old('message') }}</textarea>
                            <div class="form-text">
                                <span id="charCount">0</span>/2000 characters
                            </div>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="card-footer">
                        <div class="d-flex justify-content-between">
                            <a href="{{ $client ? route('tenant.clients.show', $client) : route('tenant.chat.index') }}" 
                               class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>Cancel
                            </a>
                            <button type="submit" class="btn btn-primary" id="sendBtn">
                                <i class="fas fa-paper-plane me-1"></i>Send Message
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const messageTextarea = document.getElementById('message');
    const charCount = document.getElementById('charCount');
    const sendBtn = document.getElementById('sendBtn');
    const form = document.getElementById('messageForm');
    
    // Character counter
    messageTextarea.addEventListener('input', function() {
        const count = this.value.length;
        charCount.textContent = count;
        
        if (count > 2000) {
            charCount.classList.add('text-danger');
            sendBtn.disabled = true;
        } else {
            charCount.classList.remove('text-danger');
            sendBtn.disabled = false;
        }
    });
    
    // Form submission
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        sendBtn.disabled = true;
        sendBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Sending...';
        
        fetch(this.action, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Show success message
                const alert = document.createElement('div');
                alert.className = 'alert alert-success alert-dismissible fade show';
                alert.innerHTML = `
                    <i class="fas fa-check-circle me-2"></i>${data.message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                document.querySelector('.card-body').insertBefore(alert, document.querySelector('.card-body').firstChild);
                
                // Reset form
                messageTextarea.value = '';
                charCount.textContent = '0';
                
                // Redirect after 2 seconds
                setTimeout(() => {
                    const clientId = document.getElementById('client_id').value;
                    if (clientId) {
                        window.location.href = `/clients/${clientId}`;
                    } else {
                        window.location.href = '/chat';
                    }
                }, 2000);
            } else {
                throw new Error(data.message || 'Failed to send message');
            }
        })
        .catch(error => {
            // Show error message
            const alert = document.createElement('div');
            alert.className = 'alert alert-danger alert-dismissible fade show';
            alert.innerHTML = `
                <i class="fas fa-exclamation-circle me-2"></i>${error.message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            document.querySelector('.card-body').insertBefore(alert, document.querySelector('.card-body').firstChild);
        })
        .finally(() => {
            sendBtn.disabled = false;
            sendBtn.innerHTML = '<i class="fas fa-paper-plane me-1"></i>Send Message';
        });
    });
});
</script>
@endsection