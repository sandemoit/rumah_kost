<div class="tab-pane active">
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="report-section">
                <div class="card">
                    <div class="card-header">
                        <img src="{{ asset('assets/icon/list-keluar.png') }}" width="20" height="20" alt="expense">
                        Pengeluaran
                    </div>
                    <input type="hidden" id="detail_report_book" value="all">
                    <div class="card-body">
                        <div id="pengeluaran">
                            <table class="table table-bordered">
                                <tbody>
                                    @foreach ($pengeluarans as $pengeluaran)
                                        <tr>
                                            <td class="center" style="width: 20px;">
                                                <img class="folderreport" id="folder_{{ $pengeluaran['id'] }}"
                                                    src="{{ asset('assets/icon/folder.png') }}" width="20"
                                                    height="15" alt="folder" title="Klik untuk melihat detilnya"
                                                    onclick="slidedetail('{{ $pengeluaran['id'] }}')">
                                            </td>
                                            <td><span class="pointer hover"
                                                    onclick="slidedetail('{{ $pengeluaran['id'] }}')">
                                                    {{ $pengeluaran['nama_kontrakan'] }} </span></td>
                                            <td class="right tdmatauang">Rp</td>
                                            <td class="right tduang" id="totalKontrakan_{{ $pengeluaran['id'] }}">
                                                {{ number_format($pengeluaran['total'], 0, ',', '.') }}</td>
                                        </tr>
                                        <tr class="none" id="trsub_{{ $pengeluaran['id'] }}" style="display: none;">
                                            <td>&nbsp;</td>
                                            <td colspan="3">
                                                <div class="trsub" id="divsub_{{ $pengeluaran['id'] }}">
                                                    <table border="0" class="subreport">
                                                        <tbody>
                                                            @foreach ($pengeluaran['transaksi'] as $detail)
                                                                <tr>
                                                                    <td class="center" style="width:140px;">
                                                                        <span
                                                                            class="longdate">{{ \Illuminate\Support\Carbon::parse($detail->created_at)->format('d M Y, H:i') }}</span>
                                                                    </td>
                                                                    <td>
                                                                        {{$detail->deskripsi}}<br><em><small>Kas:
                                                                            @php
                                                                                $kamar = \App\Models\Kamar::whereIn('id', json_decode($detail->transaksiList->id_kamar))->pluck('nama_kamar')->implode(', ');
                                                                            @endphp
                                                                                {{ $kamar }}</small></em></td>
                                                                    <td class="right tdmatauang">Rp</td>
                                                                    <td class="right" style="width:96px;">{{ number_format($detail->transaksiList->nominal, 0, ',', '.') }}
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach

                                    <tr>
                                        <td class="line">&nbsp;</td>
                                        <td class="line">&nbsp;</td>
                                        <td class="right tdmatauang line">Rp</td>
                                        <td class="right tduang line"
                                            id="">{{ number_format($total_pengeluaran, 0, ',', '.') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-12 col-sm-12">
            <div class="report-section">
                <div class="card">
                    <div class="card-header">
                        <img src="{{ asset('assets/icon/list-masuk.png') }}" width="20" height="20"
                            alt="income"> Pemasukan
                    </div>
                    <div class="card-body">
                        <div id="pemasukan">
                            <table class="table table-bordered">
                                
                                <tbody>
                                    @foreach ($pemasukans as $pemasukan)
                                        <tr>
                                            <td class="center" style="width: 20px;">
                                                <img class="folderreport" id="folder_{{ $pemasukan['id'] }}"
                                                    src="{{ asset('assets/icon/folder.png') }}" width="20"
                                                    height="15" alt="folder" title="Klik untuk melihat detilnya"
                                                    onclick="slidedetail('{{ $pemasukan['id'] }}')">
                                            </td>
                                            <td><span class="pointer hover"
                                                    onclick="slidedetail('{{ $pemasukan['id'] }}')">
                                                    {{ $pemasukan['nama_kontrakan'] }} </span></td>
                                            <td class="right tdmatauang">Rp</td>
                                            <td class="right tduang" id="totalKontrakan_{{ $pemasukan['id'] }}">
                                                {{ number_format($pemasukan['total'], 0, ',', '.') }}</td>
                                        </tr>
                                        <tr class="none" id="trsub_{{ $pemasukan['id'] }}" style="display: none;">
                                            <td>&nbsp;</td>
                                            <td colspan="3">
                                                <div class="trsub" id="divsub_{{ $pemasukan['id'] }}">
                                                    <table border="0" class="subreport">
                                                        <tbody>
                                                            @foreach ($pemasukan['transaksi'] as $detail)
                                                                <tr>
                                                                    <td class="center" style="width:140px;">
                                                                        <span
                                                                            class="longdate">{{ \Illuminate\Support\Carbon::parse($detail->created_at)->format('d M Y, H:i') }}</span>
                                                                    </td>
                                                                    <td>
                                                                        {{$detail->deskripsi}}<br><em><small>Kas:
                                                                            @php
                                                                                $kamar = \App\Models\Kamar::whereIn('id', json_decode($detail->transaksiList->id_kamar))->pluck('nama_kamar')->implode(', ');
                                                                            @endphp
                                                                                {{ $kamar }}</small></em></td>
                                                                    <td class="right tdmatauang">Rp</td>
                                                                    <td class="right" style="width:96px;">{{ number_format($detail->transaksiList->nominal, 0, ',', '.') }}
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td class="line">&nbsp;</td>
                                        <td class="line">&nbsp;</td>
                                        <td class="right tdmatauang line">Rp</td>
                                        <td class="right tduang line"
                                            id="">{{ number_format($total_pemasukan, 0, ',', '.') }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
