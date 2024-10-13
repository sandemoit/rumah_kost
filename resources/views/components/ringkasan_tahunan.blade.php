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
                                    @foreach ($dates as $date)
                                        @if ($type == 'harian')
                                            <th class="tdblue">
                                                {{ Illuminate\Support\Carbon::parse($date)->format('d') }}</th>
                                        @elseif ($type == 'bulanan')
                                            <th class="tdblue">
                                                {{ monthName($date) }}</th>
                                        @else
                                            <th class="tdblue" nowrap>{{ $date }}</th>
                                        @endif
                                    @endforeach
                                    <th class="tdblue">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pemasukans as $pemasukan)
                                    <tr>
                                        <td>{{ $pemasukan['nama_kontrakan'] }}</td>
                                        @php
                                            $totalPerKontrakan = 0;
                                        @endphp
                                        @foreach ($pemasukan['transaksi'] as $p)
                                            <td>{{ rupiah($p) }}</td>
                                            @php
                                                $totalPerKontrakan += $p;
                                            @endphp
                                        @endforeach
                                        <td>{{ rupiah($totalPerKontrakan) }}</td>
                                    </tr>
                                @endforeach
                                <!-- Add more rows as needed -->
                                <tr class="total-row">
                                    <td class="tdgray">TOTAL</td>
                                    @php
                                        $sumGrandTotalPemasukans = 0;
                                    @endphp
                                    @foreach ($grandTotalPemasukans as $grandTotal)
                                        <td class="tdgray">{{ rupiah($grandTotal) }}</td>
                                        @php
                                            $sumGrandTotalPemasukans += $grandTotal;
                                        @endphp
                                    @endforeach
                                    <td class="tdgray">{{ rupiah($sumGrandTotalPemasukans) }}</td>
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
                            alt="income"> Pengeluaran
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="tdred">Kontrakan</th>
                                    @foreach ($dates as $date)
                                        @if ($type == 'harian')
                                            <th class="tdred">
                                                {{ Illuminate\Support\Carbon::parse($date)->format('d') }}</th>
                                        @elseif ($type == 'bulanan')
                                            <th class="tdred">
                                                {{ monthName($date) }}</th>
                                        @else
                                            <th class="tdred" nowrap>{{ $date }}</th>
                                        @endif
                                    @endforeach
                                    <th class="tdred">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($pengeluarans as $pengeluaran)
                                    <tr>
                                        <td>{{ $pengeluaran['nama_kontrakan'] }}</td>
                                        @php
                                            $totalPerKontrakan = 0;
                                        @endphp
                                        @foreach ($pengeluaran['transaksi'] as $p)
                                            <td>{{ rupiah($p) }}</td>
                                            @php
                                                $totalPerKontrakan += $p;
                                            @endphp
                                        @endforeach
                                        <td>{{ rupiah($totalPerKontrakan) }}</td>
                                    </tr>
                                @endforeach
                                <!-- Add more rows as needed -->
                                <tr class="total-row">
                                    <td class="tdgray">TOTAL</td>
                                    @php
                                        $sumGrandTotalPengeluarans = 0;
                                    @endphp
                                    @foreach ($grandTotalPengeluarans as $grandTotal)
                                        <td class="tdgray">{{ rupiah($grandTotal) }}</td>
                                        @php
                                            $sumGrandTotalPengeluarans += $grandTotal;
                                        @endphp
                                    @endforeach
                                    <td class="tdgray">{{ rupiah($sumGrandTotalPengeluarans) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 col-sm-12 d-none">
            <div class="report-section">
                <div class="card">
                    <div class="card-header">
                        <img src="{{ asset('assets/icon/list-masuk.png') }}" width="20" height="20"
                            alt="income"> Profit
                    </div>
                    <div class="card-body table-responsive">
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th class="tdgray">Kontrakan</th>
                                    @foreach ($dates as $date)
                                        @if ($type == 'harian')
                                            <th class="tdgray">
                                                {{ Illuminate\Support\Carbon::parse($date)->format('d') }}</th>
                                        @elseif ($type == 'bulanan')
                                            <th class="tdgray">
                                                {{ monthName($date) }}</th>
                                        @else
                                            <th class="tdgray" nowrap>{{ $date }}</th>
                                        @endif
                                    @endforeach
                                    <th class="tdgray">Total</th>
                                </tr>
                            </thead>
                            <tbody>

                                @foreach ($profits as $profit)
                                    <tr>
                                        <td>{{ $profit['nama_kontrakan'] }}</td>
                                        @php
                                            $totalPerKontrakan = 0;
                                        @endphp
                                        @foreach ($profit['transaksi'] as $p)
                                            <td>{{ rupiah($p) }}</td>
                                            @php
                                                $totalPerKontrakan += $p;
                                            @endphp
                                        @endforeach
                                        <td>{{ rupiah($totalPerKontrakan) }}</td>
                                    </tr>
                                @endforeach
                                <!-- Add more rows as needed -->
                                <tr class="total-row">
                                    <td class="tdgray">TOTAL</td>
                                    <td class="tdgray">{{ $profits->sum('qty') }}</td>
                                    @php
                                        $sumGrandTotalProfit = 0;
                                    @endphp
                                    @foreach ($grandTotalProfits as $grandTotal)
                                        <td class="tdgray">{{ rupiah($grandTotal) }}</td>
                                        @php
                                            $sumGrandTotalProfit += $grandTotal;
                                        @endphp
                                    @endforeach
                                    <td class="tdgray">{{ rupiah($sumGrandTotalProfit) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
