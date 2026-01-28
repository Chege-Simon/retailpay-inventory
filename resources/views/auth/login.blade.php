@extends('app_default')

@section('title', 'Login')

@section('content')
<div class="container my-5">
    <div class="row">
        <!-- Normal login form -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="email" class="form-label"><i class="bi bi-envelope"></i> Email</label>
                            <input type="email" class="form-control" id="email" name="email" required autofocus>
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label"><i class="bi bi-lock"></i> Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-box-arrow-in-right"></i> Login
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Quick demo logins -->
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <h6 class="text-center mb-3">Quick Demo Logins</h6>
                    <div class="d-grid gap-2">
                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <input type="hidden" name="email" value="admin@kkwholesalers.com">
                            <input type="hidden" name="password" value="password123">
                            <button type="submit" class="btn btn-danger">
                                <i class="bi bi-shield-lock"></i> Admin
                            </button>
                        </form>

                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <input type="hidden" name="email" value="john.mwangi@kkwholesalers.com">
                            <input type="hidden" name="password" value="password123">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-person-badge"></i> Branch Manager (John) {Branch A}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <input type="hidden" name="email" value="mary.njeri@kkwholesalers.com">
                            <input type="hidden" name="password" value="password123">
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-person-badge"></i> Branch Manager (Mary) {Branch B}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <input type="hidden" name="email" value="peter.kamau@kkwholesalers.com">
                            <input type="hidden" name="password" value="password123">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-shop"></i> Store Manager (Peter){Branch 1 Store 1A}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <input type="hidden" name="email" value="alice.wanjiku@kkwholesalers.com">
                            <input type="hidden" name="password" value="password123">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-shop"></i> Store Manager (Alice){Branch 2  Store 2A}
                            </button>
                        </form>

                        <form method="POST" action="{{ route('login') }}">
                            @csrf
                            <input type="hidden" name="email" value="james.ochieng@kkwholesalers.com">
                            <input type="hidden" name="password" value="password123">
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-shop"></i> Store Manager (James){Branch 2  Store 2B}
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
