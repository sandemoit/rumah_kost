<div class="tab-pane active">
    <br>
    <div class="row">
        <div class="col-md-12 col-lg-6 col-sm-12">
            <table class="table table-bordered">
                <tbody>
                    {{-- <tr>
                        <td class="tdgray">Saldo Awal Tahun</td>
                        <td class="center tdplusmin tdgray">&nbsp;</td>
                        <td class="right tdmatauang tdgray">Rp</td>
                        <td class="right tduang tdgray" id="saldo_awal_tahun">0,00</td>
                    </tr> --}}
                    <tr>
                        <td>&nbsp;</td>
                        <td class="center tdplusmin">&nbsp;</td>
                        <td class="right tdmatauang">&nbsp;</td>
                        <td class="right tduang">&nbsp;</td>
                    </tr>
                    <tr>
                        <td class="listmasuk">Semua Pemasukan</td>
                        <td class="center tdplusmin listmasuk">(+)</td>
                        <td class="right tdmatauang listmasuk">Rp</td>
                        <td class="right tduang listmasuk" id="semua_pemasukan">0,00</td>
                    </tr>
                    <tr>
                        <td class="listkeluar">Semua Pengeluaran</td>
                        <td class="center tdplusmin listkeluar">(-)</td>
                        <td class="right tdmatauang listkeluar">Rp</td>
                        <td class="right tduang listkeluar" id="semua_pengeluaran">0,00</td>
                    </tr>
                    <tr>
                        <td class="right line">Akumulasi</td>
                        <td class="right line tdplusmin">&nbsp;</td>
                        <td class="right line tdmatauang">Rp</td>
                        <td class="right line tduang" id="akumulasi">0,00</td>
                    </tr>
                    <tr>
                        <td>&nbsp;</td>
                        <td class="tdplusmin">&nbsp;</td>
                        <td class="right tdmatauang">&nbsp;</td>
                        <td class="right tduang">&nbsp;</td>
                    </tr>
                    {{-- <tr>
                        <td class="tdgray">Saldo Akhir Tahun</td>
                        <td class="tdgray">&nbsp;</td>
                        <td class="right tdmatauang tdgray">Rp</td>
                        <td class="right tduang tdgray" id="saldo_akhir_tahun">0,00</td>
                    </tr> --}}
                </tbody>
            </table>
        </div>
        <div class="col-md-12 col-lg-6 col-sm-12">
            <div class="report-chart">
                <!-- Add chart library and code here -->
                <canvas id="reportChart"></canvas>
            </div>
        </div>
    </div>
</div>
