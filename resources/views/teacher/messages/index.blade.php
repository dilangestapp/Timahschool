<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messagerie enseignant - TIMAH ACADEMY</title>
    <style>
        body{margin:0;font-family:Arial,Helvetica,sans-serif;background:#f5f7fb;color:#0f172a;min-height:100vh;display:grid;place-items:center;padding:24px}
        .card{max-width:760px;width:100%;background:#fff;border:1px solid #e2e8f0;border-radius:24px;padding:28px;box-shadow:0 18px 45px rgba(15,23,42,.08)}
        .badge{display:inline-flex;padding:8px 12px;border-radius:999px;background:#dcfce7;color:#166534;font-weight:800;margin-bottom:14px}
        h1{margin:0 0 10px;font-size:32px}.text{color:#64748b;font-size:17px;line-height:1.6}.actions{margin-top:22px;display:flex;gap:12px;flex-wrap:wrap}.btn{display:inline-flex;align-items:center;justify-content:center;min-height:44px;padding:0 16px;border-radius:14px;background:#0f2a69;color:white;text-decoration:none;font-weight:900}.btn.secondary{background:#e2e8f0;color:#0f172a}
    </style>
</head>
<body>
    <main class="card">
        <div class="badge">Messagerie stable</div>
        <h1>Messagerie enseignant</h1>
        <p class="text">Cette page s’affiche sans layout Blade, sans chargement des conversations et sans dépendance à l’ancienne interface WhatsApp. Si cette page s’ouvre, le problème vient du layout enseignant ou de l’ancienne interface, pas de la route.</p>
        <div class="actions">
            <a class="btn" href="/teacher/dashboard">Retour tableau de bord</a>
            <a class="btn secondary" href="/login">Connexion</a>
        </div>
    </main>
</body>
</html>
