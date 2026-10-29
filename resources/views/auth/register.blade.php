@extends('layouts.portal')

@section('content')
<main class="portal-main portal-registration">
    @include('components.login_intro')
    <section class="login-card portal-card" aria-labelledby="registration-heading">

        @if (session('status'))
        <div class="portal-registration-success" role="status">
            <div class="portal-account-icon" aria-hidden="true"><i class="bi bi-check-lg"></i></div>
            <span class="portal-card-kicker">YOU'RE PART OF THE COMMUNITY</span>
            <h2 id="registration-heading">Account created.</h2>
            <p>{{ session('status') }}</p>
            <div class="portal-registration-notice"><i class="bi bi-person-check" aria-hidden="true"></i><span>Need help getting approved? Contact your adviser.</span></div>
            <a href="{{ route('login') }}" class="portal-submit">Back to Sign In <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
        </div>
        @else

        <a href="{{ route('login') }}" class="portal-back-link"><i class="bi bi-arrow-left" aria-hidden="true"></i> Back to sign in</a>
        <div class="portal-card-heading">
            <div class="portal-account-icon" aria-hidden="true"><i class="bi bi-person-plus"></i></div>
            <span class="portal-card-kicker">JOIN THE CICS COMMUNITY</span>
            <h2 id="registration-heading">Your voice belongs here.</h2>
            <p>Create your student account to get started.</p>
        </div>

        @if ($errors->any())
        <div class="portal-error" role="alert">
            @foreach ($errors->all() as $err)
            <div><i class="bi bi-exclamation-circle me-1"></i>{{ $err }}</div>
            @endforeach
        </div>
        @endif

        @if (session('otp_sent'))
        <div class="portal-registration-notice" role="status">{{ session('otp_sent') }}</div>
        @endif
        @if (! session('registration_otp_email'))
        <form method="POST" action="{{ route('register.store') }}" id="regForm">
            @csrf
            <div class="portal-form-section"><span>Student details</span><small>All fields are required</small></div>

            <!-- Full Name -->
            <div class="mb-3">
                <label class="form-label-dark" for="fullNameField">Full name</label>
                <div style="position:relative;">
                    <i class="bi bi-person" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);"></i>
                    <input type="text" name="name" id="fullNameField" class="form-control-dark" autocomplete="name"
                           style="padding-left:36px;"
                           value="{{ old('name') }}"
                           placeholder="Last Name, First Name M.I."
                           required maxlength="150">
                </div>
            </div>

            <div class="mb-3">
                <label class="form-label-dark" for="emailField">GSuite email</label>
                <div style="position:relative;">
                    <i class="bi bi-envelope" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);"></i>
                    <input type="email" name="email" id="emailField" class="form-control-dark" autocomplete="username" aria-describedby="emailHint"
                           style="padding-left:36px;"
                           value="{{ old('email') }}"
                           placeholder="23-01041@g.batstate-u.edu.ph"
                           required>
                </div>
                <p class="portal-field-hint" id="emailHint">We’ll send a verification code after you submit your student details.</p>
            </div>

            <!-- Year level -->
            <div class="mb-3">
                <label class="form-label-dark" for="yearSelect">Year level</label>
                <div style="position:relative;">
                    <i class="bi bi-mortarboard" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);z-index:1;"></i>
                    <select name="year_level" id="yearSelect" class="form-control-dark" style="padding-left:36px;" required>
                        <option value="">— Select year level —</option>
                        @foreach (range(1, 4) as $yearLevel)
                            <option value="{{ $yearLevel }}" {{ old('year_level') == $yearLevel ? 'selected' : '' }}>Year {{ $yearLevel }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Majors for Years 3 and 4 -->
            <div class="mb-3" id="trackRow" style="display:none;">
                <span class="form-label-dark" id="trackLabel">Major</span>
                <div class="portal-track-options" role="group" aria-labelledby="trackLabel">
                    <button type="button" id="trackBA" class="track-option" onclick="selectTrack('BA')" aria-pressed="false">
                        <i class="bi bi-graph-up-arrow" aria-hidden="true"></i><strong>Business Analytics</strong><small>BA Major</small>
                    </button>
                    <button type="button" id="trackNT" class="track-option" onclick="selectTrack('NT')" aria-pressed="false">
                        <i class="bi bi-hdd-network" aria-hidden="true"></i><strong>Network Technology</strong><small>NT Major</small>
                    </button>
                </div>
                <input type="hidden" name="track" id="selectedTrack" value="{{ old('track') }}">
            </div>

            <!-- Semester -->
            <div class="mb-3" id="semesterRow" style="display:none;">
                <label class="form-label-dark" for="semesterSelect">Semester</label>
                <div style="position:relative;">
                    <i class="bi bi-calendar3" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);z-index:1;"></i>
                    <select name="semester" id="semesterSelect" class="form-control-dark" style="padding-left:36px;" required disabled>
                        <option value="">— Select semester —</option>
                        <option value="1" {{ old('semester') == 1 ? 'selected' : '' }}>First Semester</option>
                        <option value="2" {{ old('semester') == 2 ? 'selected' : '' }}>Second Semester</option>
                    </select>
                </div>
            </div>

            <!-- Block Section -->
            <div class="mb-3" id="blockRow" style="display:none;">
                <label class="form-label-dark" for="blockSelect">Block</label>
                <div style="position:relative;">
                    <i class="bi bi-grid" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);z-index:1;"></i>
                    <select name="block_id" id="blockSelect" class="form-control-dark" style="padding-left:36px;" required disabled>
                        <option value="">— Select your block —</option>
                        @php
                            $blockCounters = [];
                        @endphp
                        @foreach ($blocks as $b)
                            @php
                                $key = $b->year_level . '-' . $b->semester . '-' . ($b->track ?? '');
                                $blockCounters[$key] = ($blockCounters[$key] ?? 0) + 1;
                                $blockNum = $blockCounters[$key];
                            @endphp
                            <option value="{{ $b->id }}"
                                    data-year="{{ $b->year_level }}"
                                    data-semester="{{ $b->semester }}"
                                    data-track="{{ $b->track ?? '' }}"
                                    {{ old('block_id') == $b->id ? 'selected' : '' }}>
                                Block {{ $blockNum }}@if ($b->track) — {{ $b->majorName() }}@endif
                            </option>
                        @endforeach
                    </select>
                </div>
                <p class="portal-field-hint">Choose the section you are currently enrolled in.</p>
            </div>

            <div class="portal-form-section portal-form-section--security"><span>Account security</span><i class="bi bi-lock" aria-hidden="true"></i></div>
            <!-- Password -->
            <div class="mb-3">
                <label class="form-label-dark" for="pwd1">Password</label>
                <div style="position:relative;">
                    <i class="bi bi-lock" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);"></i>
                    <input type="password" name="password" id="pwd1" class="form-control-dark" autocomplete="new-password"
                           style="padding-left:36px;padding-right:40px;"
                           placeholder="Minimum 8 characters" required minlength="8">
                    <button type="button" onclick="togglePwd('pwd1','eye1',this)" aria-label="Show password" aria-pressed="false"
                            style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;padding:0;">
                        <i class="bi bi-eye" id="eye1"></i>
                    </button>
                </div>
            </div>

            <!-- Confirm Password -->
            <div class="mb-4">
                <label class="form-label-dark" for="pwd2">Confirm password</label>
                <div style="position:relative;">
                    <i class="bi bi-lock-fill" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-muted);"></i>
                    <input type="password" name="password_confirmation" id="pwd2" class="form-control-dark" autocomplete="new-password" aria-describedby="pwdMatch"
                           style="padding-left:36px;padding-right:40px;"
                           placeholder="Re-enter your password" required>
                    <button type="button" onclick="togglePwd('pwd2','eye2',this)" aria-label="Show confirm password" aria-pressed="false"
                            style="position:absolute;right:10px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;padding:0;">
                        <i class="bi bi-eye" id="eye2"></i>
                    </button>
                </div>
                <div id="pwdMatch" class="portal-field-hint" aria-live="polite"></div>
            </div>

            <button type="submit" class="portal-submit">Continue to email verification <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
        </form>

        <div class="portal-registration-notice"><i class="bi bi-person-check" aria-hidden="true"></i><span>Your adviser will verify your account before you can vote.</span></div>
        @else
        <div class="portal-form-section"><span>Verify your email</span><small>Check your inbox</small></div>
        <p class="portal-field-hint">Your student details are saved. We sent a 6-digit code to <strong>{{ session('registration_otp_email') }}</strong>. The code expires in 10 minutes.</p>
        <form method="POST" action="{{ route('register.otp.verify') }}" class="mb-3">
            @csrf
            <div class="mb-3">
                <label class="form-label-dark" for="otpField">Email verification code</label>
                <input type="text" name="otp" id="otpField" class="form-control-dark" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}" maxlength="6" required>
            </div>
            <button type="submit" class="portal-submit">Verify code &amp; create account <i class="bi bi-arrow-right" aria-hidden="true"></i></button>
        </form>
        <form method="POST" action="{{ route('register.otp.send') }}" class="mb-3">
            @csrf
            <button type="submit" class="portal-link" style="background:none;border:0;padding:0;">Resend code</button>
        </form>
        @endif

        @if (session('registration_otp_email'))
        <form method="POST" action="{{ route('register.otp.reset') }}" id="resetEmailVerificationForm">
            @csrf
        </form>
        <button type="submit" form="resetEmailVerificationForm" class="portal-link" style="background:none;border:0;padding:0;">Change student details</button>
        @endif

        <div class="portal-card-note">
            <span>Already have an account? </span>
            <a href="{{ route('login') }}" class="portal-link">Sign In</a>
        </div>

        @endif
    </section>
</main>
@endsection

@push('scripts')
<script>
const yearSelect    = document.getElementById('yearSelect');
const trackRow      = document.getElementById('trackRow');
const semesterRow   = document.getElementById('semesterRow');
const semesterSelect = document.getElementById('semesterSelect');
const blockRow      = document.getElementById('blockRow');
const blockSelect   = document.getElementById('blockSelect');
const selectedTrack = document.getElementById('selectedTrack');

if (yearSelect) {
    yearSelect.addEventListener('change', function () {
        const yr = this.value;
        if (selectedTrack) selectedTrack.value = '';
        resetTrackUI();
        resetSemesterAndBlock();

        if (!yr) {
            if (trackRow) trackRow.style.display = 'none';
            if (semesterRow) semesterRow.style.display = 'none';
            return;
        }

        if (yr === '3' || yr === '4') {
            if (trackRow) trackRow.style.display = 'block';
        } else {
            if (trackRow) trackRow.style.display = 'none';
            showSemesterSelect();
        }
    });
}

function selectTrack(track) {
    if (selectedTrack) selectedTrack.value = track;
    resetTrackUI();

    const yr = yearSelect ? yearSelect.value : '';
    const chosen = document.getElementById('track' + track);
    if (chosen) {
        chosen.classList.add('is-selected');
        chosen.setAttribute('aria-pressed', 'true');
    }

    resetSemesterAndBlock();
    if (yr === '3' || yr === '4') {
        showSemesterSelect();
    }
}

function resetTrackUI() {
    ['trackBA','trackNT'].forEach(id => {
        const el = document.getElementById(id);
        if (el) { el.classList.remove('is-selected'); el.setAttribute('aria-pressed', 'false'); }
    });
}

function resetSemesterAndBlock() {
    if (semesterSelect) {
        semesterSelect.value = '';
        semesterSelect.disabled = true;
    }
    if (semesterRow) semesterRow.style.display = 'none';
    if (blockSelect) {
        blockSelect.value = '';
        blockSelect.disabled = true;
    }
    if (blockRow) blockRow.style.display = 'none';
    filterBlocks('', '', '');
}

function showSemesterSelect() {
    if (semesterRow) semesterRow.style.display = 'block';
    if (semesterSelect) semesterSelect.disabled = false;
}

if (semesterSelect) {
    semesterSelect.addEventListener('change', function () {
        const yr = yearSelect ? yearSelect.value : '';
        const track = selectedTrack ? selectedTrack.value : '';
        if (!yr || !this.value || ((yr === '3' || yr === '4') && !track)) {
            if (blockRow) blockRow.style.display = 'none';
            if (blockSelect) blockSelect.disabled = true;
            return;
        }

        filterBlocks(yr, track, this.value);
        if (blockRow) blockRow.style.display = 'block';
        if (blockSelect) blockSelect.disabled = false;
    });
}

function filterBlocks(year, track, semester) {
    if (!blockSelect) return;
    const opts = blockSelect.querySelectorAll('option[data-year]');
    opts.forEach(opt => {
        const matchYear  = opt.dataset.year  === year;
        const matchSemester = opt.dataset.semester === semester;
        const matchTrack = track === '' ? opt.dataset.track === '' : opt.dataset.track === track;
        const isMatch = matchYear && matchSemester && matchTrack;
        opt.style.display = isMatch ? '' : 'none';
        opt.disabled = !isMatch;
    });
    blockSelect.value = '';
}

function togglePwd(id, iconId, button) {
    const f = document.getElementById(id);
    const i = document.getElementById(iconId);
    if (!f || !i) return;
    f.type = f.type === 'password' ? 'text' : 'password';
    i.className = f.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
    const field = id === 'pwd2' ? 'confirm password' : 'password';
    button.setAttribute('aria-label', (f.type === 'password' ? 'Show ' : 'Hide ') + field);
    button.setAttribute('aria-pressed', f.type === 'text' ? 'true' : 'false');
}

const pwd1 = document.getElementById('pwd1');
const pwd2 = document.getElementById('pwd2');
if (pwd1 && pwd2) {
    function updatePasswordMatch() {
        const msg = document.getElementById('pwdMatch');
        if (!msg) return;
        if (!pwd2.value) { msg.textContent = ''; msg.classList.remove('is-match', 'is-mismatch'); pwd2.setCustomValidity(''); return; }
        const matches = pwd2.value === pwd1.value;
        msg.textContent = matches ? 'Passwords match' : 'Passwords do not match';
        msg.classList.toggle('is-match', matches);
        msg.classList.toggle('is-mismatch', !matches);
        pwd2.setCustomValidity(matches ? '' : 'Passwords do not match.');
    }
    pwd1.addEventListener('input', updatePasswordMatch);
    pwd2.addEventListener('input', updatePasswordMatch);
}

(function () {
    const yearLevel = @json(old('year_level'));
    const track = @json(old('track'));
    const semester = @json(old('semester'));
    const blockId = @json(old('block_id'));
    if (!yearSelect || !yearLevel) return;

    const year = String(yearLevel);
    const major = year === '3' || year === '4' ? String(track || '') : '';
    yearSelect.value = year;
    if (selectedTrack) selectedTrack.value = major;

    if (year === '3' || year === '4') {
        if (trackRow) trackRow.style.display = 'block';
        if (major) selectTrack(major);
    } else {
        if (trackRow) trackRow.style.display = 'none';
        showSemesterSelect();
    }

    if (semester && (year === '1' || year === '2' || major)) {
        semesterSelect.value = String(semester);
        semesterSelect.dispatchEvent(new Event('change'));
    }
    if (blockId && blockSelect) {
        const selectedBlock = Array.from(blockSelect.options).find(option => option.value === String(blockId));
        if (selectedBlock && !selectedBlock.disabled) {
            blockSelect.value = String(blockId);
        }
    }
})();
</script>
@endpush
