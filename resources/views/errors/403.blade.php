<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>403 - Accès Refusé</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background: #f5f7fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .container {
            text-align: center;
            background: white;
            border-radius: 12px;
            padding: 50px 30px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            max-width: 450px;
            width: 100%;
        }
        .code {
            font-size: 64px;
            font-weight: 700;
            color: #696cff;
            margin-bottom: 10px;
        }
        .title {
            font-size: 24px;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 12px;
        }
        .message {
            font-size: 15px;
            color: #718096;
            margin-bottom: 30px;
            line-height: 1.5;
        }
        .actions {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn {
            padding: 10px 24px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
            display: inline-block;
        }
        .btn-primary {
            background: #696cff;
            color: white;
        }
        .btn-primary:hover {
            background: #5a5dff;
            color: white;
        }
        .btn-secondary {
            background: #f7fafc;
            color: #4a5568;
            border: 1px solid #e2e8f0;
        }
        .btn-secondary:hover {
            background: #edf2f7;
            color: #2d3748;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="code">403</div>
        <h1 class="title">Accès Refusé</h1>
        <p class="message">
            @if(isset($exception) && $exception->getMessage())
                {{ $exception->getMessage() }}
            @else
                Vous n'avez pas la permission d'accéder à cette ressource.
            @endif
        </p>
        <div class="actions">
            <a href="{{ route('platform-admin.dashboard') }}" class="btn btn-primary">Retour au Dashboard</a>
            <a href="javascript:history.back()" class="btn btn-secondary">Page Précédente</a>
        </div>
    </div>
</body>
</html>

