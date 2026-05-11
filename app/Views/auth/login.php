<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin Sekolah</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            min-height: 100vh;
            background: #f4f8fb;
            color: #0d2f4f;
        }

        .login-shell {
            min-height: 100vh;
            display: grid;
            grid-template-columns: 1.05fr 0.95fr;
        }

        .login-visual {
            background:
                linear-gradient(90deg, rgba(16, 79, 134, 0.96), rgba(16, 79, 134, 0.78)),
                url('https://images.unsplash.com/photo-1523050854058-8df90110c9f1?auto=format&fit=crop&w=1400&q=85') center/cover;
            color: #fff;
            display: flex;
            align-items: center;
            padding: 4rem;
            position: relative;
            overflow: hidden;
        }

        .login-visual::after {
            content: "";
            position: absolute;
            right: -90px;
            bottom: -130px;
            width: 340px;
            height: 340px;
            background: #f5b719;
            clip-path: polygon(50% 0, 100% 100%, 0 100%);
        }

        .login-visual h1 {
            max-width: 620px;
            font-size: clamp(2.4rem, 4vw, 4.6rem);
            font-weight: 900;
            line-height: 1.12;
            letter-spacing: 0;
        }

        .login-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            background: #fff;
        }

        .login-card {
            width: min(100%, 430px);
        }

        .brand-mark {
            width: 58px;
            height: 58px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            background: #e9f4fb;
            color: #104f86;
            font-size: 1.7rem;
            margin-bottom: 1.5rem;
        }

        .btn-login {
            background: #104f86;
            border: 0;
            border-radius: 8px;
            font-weight: 800;
            padding: 0.85rem 1rem;
        }

        .form-control {
            border-radius: 8px;
            padding: 0.8rem 0.9rem;
        }

        @media (max-width: 991.98px) {
            .login-shell {
                grid-template-columns: 1fr;
            }

            .login-visual {
                min-height: 300px;
                padding: 2rem;
            }
        }
    </style>
</head>

<body>
    <main class="login-shell">
        <section class="login-visual">
            <div class="position-relative">
                <p class="text-warning fw-bold text-uppercase mb-3">Panel Admin Sekolah</p>
                <h1>Kelola chat, SPMB, dan layanan sekolah dari satu dashboard.</h1>
            </div>
        </section>
        <section class="login-panel">
            <div class="login-card">
                <span class="brand-mark"><i class="fa-solid fa-graduation-cap"></i></span>
                <h2 class="fw-bold mb-2">Login Admin</h2>
                <p class="text-muted mb-4">Masuk untuk membuka dashboard sekolah.</p>

                <?php if (session('error')): ?>
                    <div class="alert alert-danger"><?= esc(session('error')) ?></div>
                <?php endif; ?>

                <?php if (session('success')): ?>
                    <div class="alert alert-success"><?= esc(session('success')) ?></div>
                <?php endif; ?>

                <form action="<?= site_url('admin/login') ?>" method="post">
                    <div class="mb-3">
                        <label class="form-label" for="email">Email</label>
                        <input class="form-control" id="email" name="email" type="email"
                            value="<?= old('email', 'admin@sekolah.test') ?>" required autofocus>
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="password">Password</label>
                        <input class="form-control" id="password" name="password" type="password"
                            placeholder="Masukkan password" required>
                    </div>
                    <button class="btn btn-primary btn-login w-100" type="submit">Masuk Dashboard</button>
                </form>

                <div class="alert alert-info mt-4 mb-0">
                    Default awal: <strong>admin@sekolah.test</strong> / <strong>admin123</strong>
                </div>
            </div>
        </section>
    </main>
</body>

</html>