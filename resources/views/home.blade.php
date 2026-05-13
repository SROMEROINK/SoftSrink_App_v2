@extends('adminlte::page')
{{-- resources\views\home.blade.php --}}

@section('title', 'Centro de Operaciones')

@php
    $userName = Auth::user()->name ?? 'Equipo';
    $today = now();

    $priorityActions = [
        [
            'title' => 'Pedido del Cliente',
            'description' => 'Alta, seguimiento y edicion del pedido base de produccion.',
            'route' => route('pedido_cliente.index'),
            'button' => 'Abrir pedidos',
            'icon' => 'fas fa-clipboard-list',
            'accent' => 'success',
        ],
        [
            'title' => 'Listado OF',
            'description' => 'Vista consolidada para controlar pedido, MP, fabricacion y entrega.',
            'route' => route('listado_of.index'),
            'button' => 'Ver resumen OF',
            'icon' => 'fas fa-stream',
            'accent' => 'primary',
        ],
        [
            'title' => 'Registro de Fabricacion',
            'description' => 'Carga operativa de produccion y consulta de avances por OF.',
            'route' => route('fabricacion.index'),
            'button' => 'Ir a fabricacion',
            'icon' => 'fas fa-industry',
            'accent' => 'info',
        ],
    ];

    $workflows = [
        [
            'eyebrow' => 'Planificacion',
            'title' => 'Programacion de la produccion',
            'description' => 'Concentra el flujo inicial desde el pedido hasta la definicion de fechas.',
            'icon' => 'fas fa-project-diagram',
            'links' => [
                ['label' => 'Pedido del Cliente', 'route' => route('pedido_cliente.index')],
                ['label' => 'Fechas de Produccion', 'route' => route('fechas_of.index')],
                ['label' => 'Planificacion MP', 'route' => route('pedido_cliente_mp.index')],
            ],
        ],
        [
            'eyebrow' => 'Operacion',
            'title' => 'Seguimiento de planta',
            'description' => 'Acceso rapido a fabricacion, consolidado de OF y control de entregas.',
            'icon' => 'fas fa-cogs',
            'links' => [
                ['label' => 'Registro de Fabricacion', 'route' => route('fabricacion.index')],
                ['label' => 'Listado OF', 'route' => route('listado_of.index')],
                ['label' => 'Listado de Entregas', 'route' => route('entregas_productos.index')],
            ],
        ],
        [
            'eyebrow' => 'Base de datos',
            'title' => 'Catalogos y abastecimiento',
            'description' => 'Actualiza productos y revisa el movimiento de materia prima.',
            'icon' => 'fas fa-database',
            'links' => [
                ['label' => 'Listado de Productos', 'route' => route('productos.index')],
                ['label' => 'Ingresos de MP', 'route' => route('mp_ingresos.index')],
                ['label' => 'Stock MP', 'route' => route('mp_stock.index')],
            ],
        ],
    ];

    $quickLinks = [
        ['label' => 'Crear Pedido', 'route' => route('pedido_cliente.create'), 'class' => 'btn-success'],
        ['label' => 'Cargar Fabricacion', 'route' => route('fabricacion.create'), 'class' => 'btn-primary'],
        ['label' => 'Registrar Entrega', 'route' => route('entregas_productos.create'), 'class' => 'btn-danger'],
        ['label' => 'Ingresar MP', 'route' => route('mp_ingresos.create'), 'class' => 'btn-dark'],
    ];
@endphp

@section('content_header')
    <div class="dashboard-header">
        <div>
            <div class="dashboard-eyebrow">Centro de operaciones</div>
            <h1>Inicio del sistema</h1>
            <p>Un acceso inicial mas claro para entrar directo al flujo operativo de SoftSrink.</p>
        </div>
        <div class="dashboard-header__meta">
            <span>{{ $today->translatedFormat('l, d \\d\\e F \\d\\e Y') }}</span>
        </div>
    </div>
@stop

@section('content')
    <div class="dashboard-home">
        <section class="dashboard-hero">
            <div class="dashboard-hero__content">
                <span class="dashboard-pill">Bienvenido, {{ $userName }}</span>
                <h2>Tablero de inicio para entrar rapido a planificacion, produccion y entregas.</h2>
                <p>
                    Te propongo usar esta pantalla como punto de partida diario: resumen general arriba,
                    accesos principales al centro y flujos organizados abajo.
                </p>
                <div class="dashboard-hero__actions">
                    @foreach($priorityActions as $action)
                        <a href="{{ $action['route'] }}" class="btn btn-{{ $action['accent'] }} btn-dashboard-main">
                            <i class="{{ $action['icon'] }} mr-2"></i>{{ $action['button'] }}
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="dashboard-hero__panel">
                <div class="dashboard-hero__panel-label">Ruta sugerida</div>
                <ol class="dashboard-steps">
                    <li>Crear o revisar pedido del cliente.</li>
                    <li>Confirmar fechas y planificacion de materia prima.</li>
                    <li>Cargar fabricacion y seguir el estado de la OF.</li>
                    <li>Registrar entrega final al cliente.</li>
                </ol>
            </div>
        </section>

        <section class="dashboard-metrics row">
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="metric-card metric-card--green">
                    <span class="metric-card__label">Pedidos</span>
                    <strong>{{ number_format($metrics['pedidos'] ?? 0, 0, ',', '.') }}</strong>
                    <small>Ordenes activas registradas</small>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="metric-card metric-card--blue">
                    <span class="metric-card__label">Piezas</span>
                    <strong>{{ number_format($metrics['piezas'] ?? 0, 0, ',', '.') }}</strong>
                    <small>Total solicitado</small>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="metric-card metric-card--dark">
                    <span class="metric-card__label">Fabricacion</span>
                    <strong>{{ number_format($metrics['fabricaciones'] ?? 0, 0, ',', '.') }}</strong>
                    <small>Registros cargados</small>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="metric-card metric-card--amber">
                    <span class="metric-card__label">Productos</span>
                    <strong>{{ number_format($metrics['productos'] ?? 0, 0, ',', '.') }}</strong>
                    <small>Catalogo disponible</small>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="metric-card metric-card--slate">
                    <span class="metric-card__label">Ingresos MP</span>
                    <strong>{{ number_format($metrics['materia_prima'] ?? 0, 0, ',', '.') }}</strong>
                    <small>Entradas registradas</small>
                </div>
            </div>
            <div class="col-lg-2 col-md-4 col-sm-6">
                <div class="metric-card metric-card--teal">
                    <span class="metric-card__label">Planificacion MP</span>
                    <strong>{{ number_format($metrics['planificaciones_mp'] ?? 0, 0, ',', '.') }}</strong>
                    <small>Definiciones por OF</small>
                </div>
            </div>
        </section>

        <section class="dashboard-section">
            <div class="dashboard-section__header">
                <div>
                    <span class="dashboard-eyebrow">Accesos principales</span>
                    <h3>Modulos organizados por flujo de trabajo</h3>
                </div>
                <div class="dashboard-quick-links">
                    @foreach($quickLinks as $link)
                        <a href="{{ $link['route'] }}" class="btn {{ $link['class'] }} dashboard-quick-link">{{ $link['label'] }}</a>
                    @endforeach
                </div>
            </div>

            <div class="row">
                @foreach($workflows as $workflow)
                    <div class="col-lg-4 d-flex">
                        <article class="workflow-card">
                            <div class="workflow-card__icon">
                                <i class="{{ $workflow['icon'] }}"></i>
                            </div>
                            <span class="workflow-card__eyebrow">{{ $workflow['eyebrow'] }}</span>
                            <h4>{{ $workflow['title'] }}</h4>
                            <p>{{ $workflow['description'] }}</p>
                            <div class="workflow-card__links">
                                @foreach($workflow['links'] as $link)
                                    <a href="{{ $link['route'] }}">{{ $link['label'] }} <i class="fas fa-arrow-right"></i></a>
                                @endforeach
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        </section>

        <section class="dashboard-section dashboard-section--soft">
            <div class="dashboard-section__header">
                <div>
                    <span class="dashboard-eyebrow">Atajos</span>
                    <h3>Botonera rapida</h3>
                </div>
            </div>

            <div class="navigation-container">
                @include('partials.navigation_2')
            </div>
        </section>
    </div>
@stop

@section('css')
    <style>
        .content-wrapper {
            background:
                radial-gradient(circle at top right, rgba(22, 163, 74, 0.08), transparent 28%),
                linear-gradient(180deg, #f5f7fb 0%, #edf2f7 100%);
        }

        .content-header {
            padding-bottom: 0;
        }

        .dashboard-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
        }

        .dashboard-header h1 {
            margin: 4px 0 6px;
            font-size: 2rem;
            font-weight: 700;
            color: #0f172a;
        }

        .dashboard-header p {
            margin: 0;
            color: #475569;
            font-size: 0.98rem;
        }

        .dashboard-eyebrow {
            display: inline-flex;
            align-items: center;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            font-size: 0.75rem;
            font-weight: 700;
            color: #15803d;
        }

        .dashboard-header__meta {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 44px;
            padding: 0 16px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid rgba(148, 163, 184, 0.25);
            color: #334155;
            font-size: 0.9rem;
            font-weight: 600;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.06);
        }

        .dashboard-home {
            padding-bottom: 24px;
        }

        .dashboard-hero {
            display: grid;
            grid-template-columns: minmax(0, 2fr) minmax(280px, 1fr);
            gap: 22px;
            padding: 28px;
            margin-bottom: 22px;
            border-radius: 24px;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 45%, #14532d 100%);
            color: #f8fafc;
            box-shadow: 0 24px 48px rgba(15, 23, 42, 0.18);
        }

        .dashboard-pill {
            display: inline-flex;
            align-items: center;
            padding: 7px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            color: #dcfce7;
            font-size: 0.85rem;
            font-weight: 700;
            letter-spacing: 0.04em;
            margin-bottom: 14px;
        }

        .dashboard-hero h2 {
            max-width: 900px;
            margin: 0 0 10px;
            font-size: 2.05rem;
            line-height: 1.15;
            font-weight: 700;
        }

        .dashboard-hero p {
            max-width: 760px;
            margin: 0;
            color: rgba(248, 250, 252, 0.82);
            font-size: 1rem;
        }

        .dashboard-hero__actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 24px;
        }

        .btn-dashboard-main {
            border-radius: 12px;
            padding: 11px 18px;
            font-weight: 700;
            box-shadow: 0 14px 24px rgba(15, 23, 42, 0.15);
        }

        .dashboard-hero__panel {
            padding: 22px;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.12);
            backdrop-filter: blur(8px);
        }

        .dashboard-hero__panel-label {
            font-size: 0.82rem;
            text-transform: uppercase;
            letter-spacing: 0.14em;
            color: #bbf7d0;
            font-weight: 700;
            margin-bottom: 14px;
        }

        .dashboard-steps {
            padding-left: 18px;
            margin: 0;
            color: #e2e8f0;
        }

        .dashboard-steps li + li {
            margin-top: 10px;
        }

        .dashboard-metrics {
            margin-bottom: 8px;
        }

        .metric-card {
            height: calc(100% - 16px);
            margin-bottom: 16px;
            padding: 18px 18px 16px;
            border-radius: 18px;
            background: #fff;
            border: 1px solid rgba(226, 232, 240, 0.95);
            box-shadow: 0 14px 28px rgba(15, 23, 42, 0.06);
        }

        .metric-card__label {
            display: block;
            margin-bottom: 10px;
            color: #475569;
            font-size: 0.8rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.08em;
        }

        .metric-card strong {
            display: block;
            color: #0f172a;
            font-size: 1.8rem;
            line-height: 1;
            margin-bottom: 8px;
        }

        .metric-card small {
            color: #64748b;
            font-size: 0.84rem;
        }

        .metric-card--green { border-top: 4px solid #16a34a; }
        .metric-card--blue { border-top: 4px solid #2563eb; }
        .metric-card--dark { border-top: 4px solid #0f172a; }
        .metric-card--amber { border-top: 4px solid #d97706; }
        .metric-card--slate { border-top: 4px solid #475569; }
        .metric-card--teal { border-top: 4px solid #0f766e; }

        .dashboard-section {
            margin-top: 22px;
            padding: 24px;
            border-radius: 22px;
            background: rgba(255, 255, 255, 0.92);
            border: 1px solid rgba(226, 232, 240, 0.95);
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.05);
        }

        .dashboard-section--soft {
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        }

        .dashboard-section__header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 18px;
        }

        .dashboard-section__header h3 {
            margin: 4px 0 0;
            font-size: 1.45rem;
            font-weight: 700;
            color: #0f172a;
        }

        .dashboard-quick-links {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            align-items: stretch;
            justify-content: flex-end;
        }

        .dashboard-quick-link {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 148px;
            min-height: 42px;
            padding: 10px 16px;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 700;
            line-height: 1.1;
            white-space: nowrap;
            box-shadow: 0 10px 18px rgba(15, 23, 42, 0.08);
        }

        .dashboard-quick-link:hover {
            transform: translateY(-1px);
        }

        .workflow-card {
            width: 100%;
            padding: 22px;
            border-radius: 18px;
            background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
            border: 1px solid rgba(203, 213, 225, 0.8);
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.65);
        }

        .workflow-card__icon {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 52px;
            height: 52px;
            margin-bottom: 14px;
            border-radius: 16px;
            background: linear-gradient(135deg, #dcfce7 0%, #dbeafe 100%);
            color: #0f172a;
            font-size: 1.25rem;
        }

        .workflow-card__eyebrow {
            display: block;
            margin-bottom: 6px;
            color: #15803d;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.14em;
        }

        .workflow-card h4 {
            margin: 0 0 10px;
            color: #0f172a;
            font-size: 1.15rem;
            font-weight: 700;
        }

        .workflow-card p {
            margin: 0 0 16px;
            color: #475569;
        }

        .workflow-card__links {
            display: grid;
            gap: 10px;
        }

        .workflow-card__links a {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 11px 13px;
            border-radius: 12px;
            background: #f8fafc;
            color: #0f172a;
            font-weight: 600;
            text-decoration: none;
            border: 1px solid rgba(226, 232, 240, 0.95);
            transition: all 0.2s ease;
        }

        .workflow-card__links a:hover {
            transform: translateY(-1px);
            background: #eff6ff;
            border-color: rgba(37, 99, 235, 0.18);
            color: #1d4ed8;
        }

        .navigation-container {
            margin-top: 8px;
        }

        @media (max-width: 991.98px) {
            .dashboard-hero {
                grid-template-columns: 1fr;
            }

            .dashboard-section__header,
            .dashboard-header {
                flex-direction: column;
            }

            .dashboard-quick-links {
                justify-content: flex-start;
            }
        }

        @media (max-width: 767.98px) {
            .dashboard-hero {
                padding: 22px 18px;
            }

            .dashboard-hero h2 {
                font-size: 1.65rem;
            }

            .dashboard-section {
                padding: 18px;
            }

            .dashboard-header h1 {
                font-size: 1.65rem;
            }

            .dashboard-quick-links {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                width: 100%;
            }

            .dashboard-quick-link {
                width: 100%;
                min-width: 0;
                white-space: normal;
                text-align: center;
            }
        }
    </style>
@stop
