@extends('admin.layouts.app')

@section('title', 'Reseller Paling Loyal')

@section('content')
<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" id="resellerTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="transaksi-tab" data-bs-toggle="tab" href="#transaksi" role="tab">Berdasarkan Transaksi</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="spend-tab" data-bs-toggle="tab" href="#spend" role="tab">Berdasarkan Total Spend</a>
            </li>
        </ul>
    </div>

    <div class="card-body tab-content">
        <!-- Tab Transaksi -->
        <div class="tab-pane fade show active" id="transaksi" role="tabpanel">
            <h5 class="mb-3">Top 10 Reseller Berdasarkan Jumlah Transaksi</h5>
            <table class="table table-striped text-center">
                <thead>
                    <tr>
                        <th>Ranking</th>
                        <th>Nama Reseller</th>
                        <th>Total Transaksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($byTransaksi as $index => $reseller)
                        <tr>
                            <td>
                                {{ $index + 1 }}
                                @if ($index < 5)
                                    <i class="fas fa-star text-warning ms-1"></i>
                                @elseif ($index < 10)
                                    <i class="fas fa-star text-secondary ms-1"></i>
                                @endif
                            </td>
                            <td>{{ $reseller->nama_pelanggan }}</td>
                            <td>{{ $reseller->total_transaksi }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Tab Spend -->
        <div class="tab-pane fade" id="spend" role="tabpanel">
            <h5 class="mb-3">Top 10 Reseller Berdasarkan Total Spend</h5>
            <table class="table table-striped text-center">
                <thead>
                    <tr>
                        <th>Ranking</th>
                        <th>Nama Reseller</th>
                        <th>Total Spend (Rp)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($bySpend as $index => $reseller)
                        <tr>
                            <td>
                                {{ $index + 1 }}
                                @if ($index < 5)
                                    <i class="fas fa-star text-warning ms-1"></i>
                                @elseif ($index < 10)
                                    <i class="fas fa-star text-secondary ms-1"></i>
                                @endif
                            </td>
                            <td>{{ $reseller->nama_pelanggan }}</td>
                            <td>Rp {{ number_format($reseller->total_spend, 0, ',', '.') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
