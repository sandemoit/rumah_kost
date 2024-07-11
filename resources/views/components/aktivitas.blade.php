<div class="tab-pane active">
    <div class="row">
        <div class="col-md-12 col-sm-12">
            <div class="report-section">
                <div class="card">
                    <div class="card-header">
                        <img src="{{ asset('assets/icon/list-keluar.png') }}" width="20" height="20" alt="expense">
                        Pengeluaran
                    </div>
                    <input type="hidden" id="detail_from" value="1706806800">
                    <input type="hidden" id="detail_to" value="1706893200">
                    <input type="hidden" id="detail_report_book" value="all">
                    <div class="card-body">
                        <div class="exin" id="ex_exin">
                            <table class="table table-bordered">
                                <tbody>
                                    <tr>
                                        <td class="center" style="width: 20px;">
                                            <img class="folderreport" id="folder_4352"
                                                src="{{ asset('assets/icon/folder.png') }}" width="20"
                                                height="15" alt="folder" title="Klik untuk melihat detilnya"
                                                onclick="slidedetail('4352')">
                                        </td>
                                        <td><span class="pointer hover" onclick="slidedetail('4352')">
                                                Akasia-01 </span></td>
                                        <td class="right tdmatauang">Rp</td>
                                        <td class="right tduang">200.000,00</td>
                                    </tr>
                                    <tr class="none" id="trsub_4352" style="display: none;">
                                        <td>&nbsp;</td>
                                        <td colspan="3">
                                            <div class="trsub" id="divsub_4352">
                                                <table border="0" class="subreport">
                                                    <tbody>
                                                        <tr>
                                                            <td class="center" style="width:140px;">
                                                                <span class="longdate">02 Peb 2024, 01.34</span>
                                                                <span class="shortdate">02</span>
                                                            </td>
                                                            <td>
                                                                Perbaikan Plafon Ruang Tamu<br><em><small>Kas:
                                                                        Akasia-08<small></small></small></em><small><small>
                                                                    </small></small></td>
                                                            <td class="right tdmatauang">Rp</td>
                                                            <td class="right" style="width:96px;">200.000,00</td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="line">&nbsp;</td>
                                        <td class="line">&nbsp;</td>
                                        <td class="right tdmatauang line">Rp</td>
                                        <td class="right tduang line">500.000,00</td>
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
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td class="center" style="width: 20px;">
                                        <img class="folderreport" id="folder_4353"
                                            src="{{ asset('assets/icon/folder.png') }}" width="20" height="15"
                                            alt="folder" title="Klik untuk melihat detilnya"
                                            onclick="slidedetail('4353')">
                                    </td>
                                    <td><span class="pointer hover" onclick="slidedetail('4353')">
                                            Akasia-01 </span></td>
                                    <td class="right tdmatauang">Rp</td>
                                    <td class="right tduang">700.000,00</td>
                                </tr>
                                <tr class="none" id="trsub_4353" style="display: none;">
                                    <td>&nbsp;</td>
                                    <td colspan="3">
                                        <div class="trsub" id="divsub_4353">
                                            <table border="0" class="subreport">
                                                <tbody>
                                                    <tr>
                                                        <td class="center" style="width:140px;">
                                                            <span class="longdate">02 Peb 2024, 01.34</span>
                                                            <span class="shortdate">02</span>
                                                        </td>
                                                        <td>
                                                            Perbaikan Plafon Ruang Tamu<br><em><small>Kas:
                                                                    Akasia-08<small></small></small></em><small><small>
                                                                </small></small></td>
                                                        <td class="right tdmatauang">Rp</td>
                                                        <td class="right" style="width:96px;">200.000,00</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="line">&nbsp;</td>
                                    <td class="line">&nbsp;</td>
                                    <td class="right tdmatauang line">Rp</td>
                                    <td class="right tduang line">4.700.000,00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
