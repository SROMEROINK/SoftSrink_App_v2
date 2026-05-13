{{-- resources/views/partials/navigation_2.blade.php --}}
@php
    $items = [
        [
            'label' => 'Registro de Fabricacion',
            'route' => route('fabricacion.index'),
            'icon' => 'fas fa-industry',
            'class' => 'nav-shortcut--blue',
        ],
        [
            'label' => 'Fechas de Produccion',
            'route' => route('fechas_of.index'),
            'icon' => 'fas fa-calendar-alt',
            'class' => 'nav-shortcut--cyan',
        ],
        [
            'label' => 'Listado de Entregas',
            'route' => route('entregas_productos.index'),
            'icon' => 'fas fa-shipping-fast',
            'class' => 'nav-shortcut--red',
        ],
        [
            'label' => 'Pedido del Cliente',
            'route' => route('pedido_cliente.index'),
            'icon' => 'fas fa-clipboard-list',
            'class' => 'nav-shortcut--green',
        ],
        [
            'label' => 'Listado de Productos',
            'route' => route('productos.index'),
            'icon' => 'fas fa-boxes',
            'class' => 'nav-shortcut--amber',
        ],
        [
            'label' => 'Listado OF',
            'route' => route('listado_of.index'),
            'icon' => 'fas fa-stream',
            'class' => 'nav-shortcut--dark',
        ],
    ];
@endphp

<div class="nav-shortcuts">
    @foreach($items as $item)
        <a href="{{ $item['route'] }}" class="nav-shortcut {{ $item['class'] }}">
            <i class="{{ $item['icon'] }}"></i>
            <span>{{ $item['label'] }}</span>
        </a>
    @endforeach
</div>

<style>
    .nav-shortcuts {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(190px, 1fr));
        gap: 12px;
    }

    .nav-shortcut {
        display: flex;
        align-items: center;
        gap: 12px;
        min-height: 64px;
        padding: 14px 16px;
        border-radius: 16px;
        color: #fff;
        text-decoration: none;
        font-weight: 700;
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.1);
        transition: transform 0.2s ease, box-shadow 0.2s ease, opacity 0.2s ease;
    }

    .nav-shortcut i {
        font-size: 1rem;
    }

    .nav-shortcut span {
        line-height: 1.2;
    }

    .nav-shortcut:hover {
        color: #fff;
        text-decoration: none;
        transform: translateY(-2px);
        box-shadow: 0 18px 30px rgba(15, 23, 42, 0.14);
        opacity: 0.97;
    }

    .nav-shortcut--blue { background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); }
    .nav-shortcut--cyan { background: linear-gradient(135deg, #0891b2 0%, #0f766e 100%); }
    .nav-shortcut--red { background: linear-gradient(135deg, #dc2626 0%, #be123c 100%); }
    .nav-shortcut--green { background: linear-gradient(135deg, #16a34a 0%, #15803d 100%); }
    .nav-shortcut--amber { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
    .nav-shortcut--dark { background: linear-gradient(135deg, #334155 0%, #0f172a 100%); }
</style>
