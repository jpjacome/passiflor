<div class="user-card-body">
    <div class="user-detail">
        <i class="ph ph-envelope"></i>
        <span>{{ $consultation->email }}</span>
    </div>
    @if($consultation->phone)
    <div class="user-detail">
        <i class="ph ph-phone"></i>
        <span>{{ $consultation->phone }}</span>
    </div>
    @endif
    <div class="user-detail">
        <i class="ph ph-calendar"></i>
        <span>{{ $consultation->created_at->format('d/m/Y H:i') }}</span>
    </div>
    <div class="user-detail">
        <i class="ph ph-tag"></i>
        <span>{{ ucfirst(str_replace('-', ' ', $consultation->session_type)) }}</span>
    </div>
    @if($consultation->therapist)
    <div class="user-detail">
        <i class="ph ph-user-circle"></i>
        <span><strong>Terapeuta:</strong> {{ $consultation->therapist->name }}</span>
    </div>
    @endif
    @if($consultation->message)
    <div class="user-detail">
        <i class="ph ph-note"></i>
        <span class="consultation-message">{{ Str::limit($consultation->message, 80) }}</span>
    </div>
    @endif
    @if(auth()->user()->actingAsAdmin() && isset($therapists) && count($therapists) > 0)
    <div class="user-detail" style="margin-top: 0.75rem;">
        <form action="{{ route('admin.consultations.assignTherapist', $consultation) }}" method="POST" style="display: flex; gap: 0.5rem; align-items: center; width: 100%;">
            @csrf
            @method('PATCH')
            <select name="therapist_id" style="flex: 1; padding: 0.4rem; border-radius: 8px; border: 1px solid var(--color-3); font-family: 'Quicksand', sans-serif;">
                <option value="">-- Asignar terapeuta --</option>
                @foreach($therapists as $t)
                    <option value="{{ $t->id }}" {{ $consultation->therapist_id == $t->id ? 'selected' : '' }}>{{ $t->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn-action btn-confirm" style="padding: 0.4rem 0.8rem;">
                <i class="ph ph-check"></i>
            </button>
        </form>
    </div>
    @endif
</div>
