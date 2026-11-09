<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>AUBE TERRA Tax｜会計・税務管理システム</title>
    <meta name="description" content="AUBE TERRA Tax は、日本の中小事業者向けの会計・税務管理システムです。日々の取引記録から財務分析までをひとつに。">
    <style>
        :root {
            --bg: #F9F9FC;
            --card: #FFFFFF;
            --ink: #1C1B1F;
            --ink-soft: #52526D;
            --muted: #666687;
            --line: #EAEAF1;
            --line-soft: #E5E7EB;
            --brand: #2563EB;
            --brand-soft: #EFF6FF;
            --good: #10B981;
            --good-soft: #ECFDF5;
            --radius-lg: 24px;
            --radius-md: 16px;
            --radius-sm: 999px;
        }

        * { box-sizing: border-box; }

        html, body { margin: 0; padding: 0; }

        body {
            background: var(--bg);
            color: var(--ink);
            font-family: "Hiragino Kaku Gothic ProN", "Hiragino Sans", "Noto Sans JP", "Yu Gothic UI", Meiryo, system-ui, -apple-system, sans-serif;
            line-height: 1.75;
            -webkit-font-smoothing: antialiased;
            text-rendering: optimizeLegibility;
        }

        .wrap {
            width: min(1080px, 100% - 48px);
            margin-inline: auto;
        }

        a { color: var(--brand); }

        :focus-visible {
            outline: 3px solid rgba(37, 99, 235, .55);
            outline-offset: 3px;
            border-radius: 8px;
        }

        /* Header */
        .site-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 20px 0;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: var(--ink);
        }

        .brand-mark {
            width: 42px;
            height: 42px;
            border-radius: 14px;
            background: var(--ink);
            color: #FFFFFF;
            display: grid;
            place-items: center;
            font-weight: 700;
            font-size: 15px;
            letter-spacing: .04em;
            flex: none;
        }

        .brand-name {
            display: block;
            font-weight: 700;
            font-size: 16px;
            letter-spacing: .01em;
        }

        .brand-sub {
            display: block;
            font-size: 12px;
            color: var(--muted);
            line-height: 1.4;
        }

        .status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 7px 14px;
            border-radius: var(--radius-sm);
            background: var(--good-soft);
            color: #047857;
            font-size: 13px;
            font-weight: 600;
            white-space: nowrap;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--good);
            box-shadow: 0 0 0 4px rgba(16, 185, 129, .16);
        }

        /* Hero */
        .hero {
            padding: 48px 0 8px;
            max-width: 760px;
        }

        .eyebrow {
            display: inline-block;
            margin: 0 0 20px;
            padding: 6px 14px;
            border-radius: var(--radius-sm);
            background: var(--brand-soft);
            color: #1D4ED8;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: .02em;
        }

        .hero h1 {
            margin: 0 0 20px;
            font-size: clamp(32px, 6vw, 52px);
            line-height: 1.25;
            letter-spacing: -.02em;
            font-weight: 700;
            text-wrap: balance;
        }

        .hero p {
            margin: 0;
            max-width: 62ch;
            font-size: clamp(15px, 2.2vw, 17px);
            color: var(--ink-soft);
        }

        .hero-note {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 10px;
            margin-top: 28px;
        }

        .pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 18px;
            border-radius: var(--radius-sm);
            font-size: 14px;
            font-weight: 600;
            border: 1px solid var(--line);
            background: var(--card);
            color: var(--ink-soft);
        }

        .pill-primary {
            background: var(--ink);
            border-color: var(--ink);
            color: #FFFFFF;
        }

        /* Features */
        .features {
            margin: 56px 0 0;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 16px;
        }

        .card {
            background: var(--card);
            border: 1px solid var(--line);
            border-radius: var(--radius-lg);
            padding: 28px;
            box-shadow: 0 1px 2px rgba(28, 27, 31, .04);
        }

        .card-icon {
            width: 44px;
            height: 44px;
            border-radius: 14px;
            background: var(--brand-soft);
            color: var(--brand);
            display: grid;
            place-items: center;
            margin-bottom: 18px;
        }

        .card-icon svg { width: 22px; height: 22px; display: block; }

        .card h2 {
            margin: 0 0 8px;
            font-size: 18px;
            font-weight: 700;
            letter-spacing: -.01em;
        }

        .card p {
            margin: 0;
            font-size: 14px;
            color: var(--muted);
            line-height: 1.8;
        }

        /* Notice */
        .notice {
            margin: 24px 0 0;
            padding: 28px;
            border-radius: var(--radius-lg);
            background: var(--card);
            border: 1px solid var(--line);
            display: flex;
            gap: 20px;
            align-items: flex-start;
        }

        .notice h2 {
            margin: 0 0 6px;
            font-size: 16px;
            font-weight: 700;
        }

        .notice p {
            margin: 0;
            font-size: 14px;
            color: var(--muted);
        }

        .notice-badge {
            flex: none;
            width: 40px;
            height: 40px;
            border-radius: 13px;
            background: #F3F4F6;
            color: var(--ink-soft);
            display: grid;
            place-items: center;
            font-weight: 700;
            font-size: 13px;
        }

        /* Footer */
        .site-footer {
            margin-top: 64px;
            padding: 28px 0 40px;
            border-top: 1px solid var(--line);
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            font-size: 13px;
            color: var(--muted);
        }

        .site-footer nav {
            display: flex;
            gap: 20px;
        }

        .site-footer a { color: var(--muted); text-decoration: none; }
        .site-footer a:hover { color: var(--ink); }

        @media (max-width: 720px) {
            .features { grid-template-columns: minmax(0, 1fr); }
            .hero { padding-top: 32px; }
            .card { padding: 22px; }
            .notice { padding: 22px; }
            .site-header { padding: 16px 0; }
        }

        @media (prefers-color-scheme: dark) {
            :root {
                --bg: #121216;
                --card: #1B1B21;
                --ink: #F4F4F7;
                --ink-soft: #C7C7D4;
                --muted: #9E9EB3;
                --line: #2A2A33;
                --line-soft: #33333E;
                --brand: #7FA6FF;
                --brand-soft: #1E2A44;
                --good: #34D399;
                --good-soft: #123227;
            }
            .brand-mark { background: #F4F4F7; color: #121216; }
            .pill-primary { background: #F4F4F7; border-color: #F4F4F7; color: #121216; }
            .notice-badge { background: #2A2A33; color: #C7C7D4; }
        }
    </style>
</head>
<body>
    <div class="wrap">
        <header class="site-header">
            <a class="brand" href="/" aria-label="AUBE TERRA Tax ホーム">
                <span class="brand-mark" aria-hidden="true">AT</span>
                <span>
                    <span class="brand-name">AUBE TERRA Tax</span>
                    <span class="brand-sub">会計・税務管理システム</span>
                </span>
            </a>
            <span class="status" role="status">
                <span class="status-dot" aria-hidden="true"></span>
                API 稼働中
            </span>
        </header>

        <main>
            <section class="hero" aria-labelledby="hero-title">
                <p class="eyebrow">中小事業者のための会計プラットフォーム</p>
                <h1 id="hero-title">事業のお金を、<br>シンプルに。</h1>
                <p>
                    日々の入出金の記録から、勘定科目の管理、キャッシュフローの分析まで。
                    AUBE TERRA Tax は、日本の事業者のための会計・税務業務をひとつにまとめたシステムです。
                </p>
                <div class="hero-note">
                    <span class="pill pill-primary">取引・残高をひと目で把握</span>
                    <span class="pill">日本語対応</span>
                    <span class="pill">AI アシスタント搭載</span>
                </div>
            </section>

            <section class="features" aria-label="主な機能">
                <article class="card">
                    <div class="card-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 7h16M4 12h16M4 17h10"/>
                        </svg>
                    </div>
                    <h2>取引管理</h2>
                    <p>入金・出金をすばやく記録。日付・金額・勘定科目・支払方法をまとめて管理し、記録漏れを防ぎます。</p>
                </article>

                <article class="card">
                    <div class="card-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 5h16v14H4zM8 9h8M8 13h5"/>
                        </svg>
                    </div>
                    <h2>勘定科目</h2>
                    <p>収入・支出のカテゴリーを整理し、事業に合わせた科目構成で正確な記帳を続けられます。</p>
                </article>

                <article class="card">
                    <div class="card-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 19V5M4 19h16M8 15l4-5 3 3 5-7"/>
                        </svg>
                    </div>
                    <h2>財務分析</h2>
                    <p>キャッシュフロー、日次平均の収支、残高の推移を可視化。資金繰りの見通しを立てやすくします。</p>
                </article>

                <article class="card">
                    <div class="card-icon" aria-hidden="true">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 3l1.9 4.6L18.5 9l-4.6 1.9L12 15.5 10.1 10.9 5.5 9l4.6-1.4L12 3z"/>
                            <path d="M18 15l.9 2.1L21 18l-2.1.9L18 21l-.9-2.1L15 18l2.1-.9L18 15z"/>
                        </svg>
                    </div>
                    <h2>AI アシスタント</h2>
                    <p>「今月の支出はいくら？」と話しかけるだけ。自然な日本語で問い合わせでき、記録の下書きも作成します。</p>
                </article>
            </section>

            <section class="notice" aria-labelledby="notice-title">
                <div class="notice-badge" aria-hidden="true">API</div>
                <div>
                    <h2 id="notice-title">ご利用について</h2>
                    <p>
                        このサーバーは API のみを提供しています。ログインや各種操作は、専用のクライアントアプリから行ってください。
                        各機能は <code>/api/v1</code> 配下のエンドポイントで利用できます。
                    </p>
                </div>
            </section>
        </main>

        <footer class="site-footer">
            <span>© 2026 AUBE TERRA Tax</span>
            <nav aria-label="フッターナビゲーション">
                <a href="#">利用規約</a>
                <a href="#">プライバシーポリシー</a>
                <a href="#">お問い合わせ</a>
            </nav>
        </footer>
    </div>
</body>
</html>
