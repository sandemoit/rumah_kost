<div class="tab-pane active">
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="report-section">
                <div class="card">
                    <div class="card-header">
                        <img src="{{ asset('assets/icon/list-masuk.png') }}" width="20" height="20" alt="income">
                        Pemasukan
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="tdblue">Kontrakan</th>
                                    <th class="tdblue">Qty</th>
                                    
                                    @foreach ($dates as $date)
                                        @if ($type == 'harian')
                                        <th class="tdgray">{{ Illuminate\Support\Carbon::parse($date)->format('d') }}</th>
                                        @elseif ($type == 'bulanan')
                                        <th class="tdgray">{{ Illuminate\Support\Carbon::parse($date)->format('M') }}</th>
                                        @else
                                        <th class="tdgray">{{ $date }}</th>
                                        @endif
                                    @endforeach
                                    <th class="tdblue">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pemasukans as $pemasukan)
                                    <tr>
                                        <td>{{$pemasukan['nama_kontrakan']}}</td>
                                        <td>{{ $pemasukan['qty'] }}</td>
                                        @php
                                            $totalPerKontrakan = 0;    
                                        @endphp
                                        @foreach ($pemasukan['transaksi'] as $p)
                                            <td>{{ number_format($p, 0, '.', ',') }}</td>
                                            @php
                                                $totalPerKontrakan += $p;
                                            @endphp
                                        @endforeach
                                        <td>{{ number_format($totalPerKontrakan, 0, '.', ',') }}</td>
                                    </tr>
                                    
                                @endforeach
                                <!-- Add more rows as needed -->
                                <tr class="total-row">
                                    <td class="tdgray">TOTAL</td>
                                    <td class="tdgray">{{$pemasukans->sum('qty')}}</td>
                                    @php
                                        $sumGrandTotalPemasukans =0;
                                    @endphp
                                    @foreach ($grandTotalPemasukans as $grandTotal)
                                        <td class="tdgray">{{ number_format($grandTotal, 0, '.',',') }}</td>
                                        @php
                                            $sumGrandTotalPemasukans += $grandTotal;
                                        @endphp
                                    @endforeach
                                    <td class="tdgray">{{ number_format($sumGrandTotalPemasukans, 0,'.',',') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 col-sm-12">
            <div class="report-section">
                <div class="card">
                    <div class="card-header">
                        <img src="{{ asset('assets/icon/list-keluar.png') }}" width="20" height="20"
                            alt="expense">
                        Pengeluaran
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="tdgray">Kontrakan</th>
                                    <th class="tdgray">Qty</th>
                                    @foreach ($dates as $date)
                                        @if ($type == 'harian')
                                        <th class="tdgray">{{ Illuminate\Support\Carbon::parse($date)->format('d') }}</th>
                                        @elseif ($type == 'bulanan')
                                        <th class="tdgray">{{ Illuminate\Support\Carbon::parse($date)->format('M') }}</th>
                                        @else
                                        <th class="tdgray">{{ $date }}</th>
                                        @endif
                                    @endforeach
                                    <th class="tdgray">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                
                                @foreach ($pengeluarans as $pengeluaran)
                                    <tr>
                                        <td>{{$pengeluaran['nama_kontrakan']}}</td>
                                        <td>{{ $pengeluaran['qty'] }}</td>
                                        @php
                                            $totalPerKontrakan = 0;    
                                        @endphp
                                        @foreach ($pengeluaran['transaksi'] as $p)
                                            <td>{{ number_format($p, 0, '.', ',') }}</td>
                                            @php
                                                $totalPerKontrakan += $p;
                                            @endphp
                                        @endforeach
                                        <td>{{ number_format($totalPerKontrakan, 0, '.', ',') }}</td>
                                    </tr>
                                    
                                @endforeach
                                <!-- Add more rows as needed -->
                                <tr class="total-row">
                                    <td class="tdgray">TOTAL</td>
                                    <td class="tdgray">{{$pengeluarans->sum('qty')}}</td>
                                    @php
                                        $sumGrandTotalPengeluarans =0;
                                    @endphp
                                    @foreach ($grandTotalPengeluarans as $grandTotal)
                                        <td class="tdgray">{{ number_format($grandTotal, 0, '.',',') }}</td>
                                        @php
                                            $sumGrandTotalPengeluarans += $grandTotal;
                                        @endphp
                                    @endforeach
                                    <td class="tdgray">{{ number_format($sumGrandTotalPengeluarans, 0,'.',',') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 col-sm-12">
            <div class="report-section">
                <div class="card">
                    <div class="card-header">
                        <img src="{{ asset('assets/icon/list-masuk.png') }}" width="20" height="20"
                            alt="income"> Profit
                    </div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="tdgray">Kontrakan</th>
                                    <th class="tdgray">Qty</th>
                                    @foreach ($dates as $date)
                                        @if ($type == 'harian')
                                        <th class="tdgray">{{ Illuminate\Support\Carbon::parse($date)->format('d') }}</th>
                                        @elseif ($type == 'bulanan')
                                        <th class="tdgray">{{ Illuminate\Support\Carbon::parse($date)->format('M') }}</th>
                                        @else
                                        <th class="tdgray">{{ $date }}</th>
                                        @endif
                                    @endforeach
                                    <th class="tdgray">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                
                                @foreach ($profits as $profit)
                                    <tr>
                                        <td>{{$profit['nama_kontrakan']}}</td>
                                        <td>{{ $profit['qty'] }}</td>
                                        @php
                                            $totalPerKontrakan = 0;    
                                        @endphp
                                        @foreach ($profit['transaksi'] as $p)
                                            <td>{{ number_format($p, 0, '.', ',') }}</td>
                                            @php
                                                $totalPerKontrakan += $p;
                                            @endphp
                                        @endforeach
                                        <td>{{ number_format($totalPerKontrakan, 0, '.', ',') }}</td>
                                    </tr>
                                    
                                @endforeach
                                <!-- Add more rows as needed -->
                                <tr class="total-row">
                                    <td class="tdgray">TOTAL</td>
                                    <td class="tdgray">{{$profits->sum('qty')}}</td>
                                    @php
                                        $sumGrandTotalProfit =0;
                                    @endphp
                                    @foreach ($grandTotalProfits as $grandTotal)
                                        <td class="tdgray">{{ number_format($grandTotal, 0, '.',',') }}</td>
                                        @php
                                            $sumGrandTotalProfit += $grandTotal;
                                        @endphp
                                    @endforeach
                                    <td class="tdgray">{{ number_format($sumGrandTotalProfit, 0,'.',',') }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
