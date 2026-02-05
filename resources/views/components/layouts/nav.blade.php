<nav class="border-b border-border px-6">
    <div class="max-w-7xl mx-auto h-16 flex items-center justify-between">
        <div>
            <a href="/">
                <img src="{{ asset('images/idea-logo.png') }}" width="100" alt="Idea logo">
            </a>
        </div>

        <div class="flex gap-x-5 items-center">
            @auth
                <form action="/logout" method="POST">
                    @csrf
                    <button type="submit" class="btn" data-test="logout-button">Log out</button>
                </form>
            @else
                <a href="/login">Sign in</a>
                <a href="/register" class="btn">Register</a>
            @endauth
        </div>
    </div>
</nav>
