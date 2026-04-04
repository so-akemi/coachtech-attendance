<div class="login-form-content">
    <div class="login-form-heading">
        <h1>{{ $title }}</h1>
    </div>

    <form class="form" action="{{ $action }}" method="POST">
        @csrf
        <div class="form-group">
            <div class="form-group-title">
                <span class="form-label-item">メールアドレス</span>
            </div>
            <div class="form-group-content">
                <div class="form-input-text">
                    <input type="email" name="email" value="{{ old('email') }}" />
                </div>
                <div class="form-error">
                    @error('email')
                        {{ $message }}
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-group">
            <div class="form-group-title">
                <span class="form-label-item">パスワード</span>
            </div>
            <div class="form-group-content">
                <div class="form-input-text">
                    <input type="password" name="password" />
                </div>
                <div class="form-error">
                    @error('password')
                        {{ $message }}
                    @enderror
                </div>
            </div>
        </div>

        <div class="form-button">
            <button class="form-button-submit" type="submit">{{ $buttonText }}</button>
        </div>
    </form>

    @if ($showRegister)
        <div class="register-link">
            <a class="register-button-submit" href="{{ route('register') }}">会員登録はこちら</a>
        </div>
    @endif
</div>
