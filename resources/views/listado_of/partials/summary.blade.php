<div class="row mt-3">
    <div class="col-md-4">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ number_format((int) ($summary['total_of'] ?? 0), 0, ',', '.') }}</h3>
                <p>OF cargadas</p>
            </div>
            <div class="icon">
                <i class="fas fa-clipboard-list"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ number_format((int) ($summary['total_piezas_solicitadas'] ?? 0), 0, ',', '.') }}</h3>
                <p>Piezas solicitadas</p>
            </div>
            <div class="icon">
                <i class="fas fa-layer-group"></i>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ number_format((int) ($summary['total_piezas_fabricadas'] ?? 0), 0, ',', '.') }}</h3>
                <p>Piezas fabricadas</p>
            </div>
            <div class="icon">
                <i class="fas fa-industry"></i>
            </div>
        </div>
    </div>
</div>
