<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Presensi - {{ \Carbon\Carbon::parse($bulanDipilih . '-01')->translatedFormat('F Y') }}</title>
    <style>
        body { font-family: 'Times New Roman', Times, serif; font-size: 12px; color: #000; padding: 20px; }
        .kop-surat { position: relative; text-align: center; border-bottom: 3px solid black; padding-bottom: 15px; margin-bottom: 20px; }
        .kop-surat img { position: absolute; left: 10px; top: 0; width: 80px; height: auto; }
        .kop-surat h2, .kop-surat h3, .kop-surat p { margin: 2px 0; }
        .judul-laporan { text-align: center; font-size: 14px; font-weight: bold; text-decoration: underline; margin-bottom: 5px; }
        .sub-judul { text-align: center; font-size: 12px; margin-bottom: 25px; }
        
        /* PERBAIKAN CSS PRINT UNTUK TABEL */
        table { width: 100%; border-collapse: collapse; margin-bottom: 30px; page-break-inside: auto; }
        tr { page-break-inside: avoid; page-break-after: auto; }
        thead { display: table-header-group; } /* Memastikan header tabel berulang di halaman baru */
        
        th, td { border: 1px solid black; padding: 6px 8px; text-align: center; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-left { text-align: left !important; }
        
        /* Memastikan Tanda Tangan tidak terpotong pisah halaman */
        .tanda-tangan { width: 250px; float: right; text-align: center; margin-top: 30px; page-break-inside: avoid; }
        .tanda-tangan p { margin: 2px 0; }
        .nama-ttd { font-weight: bold; text-decoration: underline; margin-top: 60px; }
        
        /* Hapus page-break-inside: avoid dari sini agar tabel bisa mengalir normal */
        .detail-container { margin-bottom: 40px; }
        .pegawai-info { margin-bottom: 10px; font-size: 13px; font-weight: bold; }
        
        @media print {
            .no-print { display: none; }
            body { padding: 0; }
        }
    </style>
</head>
<body onload="window.print()">

    <!-- TOMBOL PRINT (Disembunyikan saat dicetak) -->
    <div class="no-print" style="text-align: right; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 8px 15px; background: #198754; color: white; border: none; cursor: pointer; border-radius: 5px;">
            🖨️ Cetak / Simpan PDF
        </button>
    </div>

    <!-- KOP SURAT -->
    <div class="kop-surat">
        <img src="{{ asset('images/Lambang_Kabupaten_Solok.png') }}" alt="Logo Kab Solok">
        <h3 style="font-size: 18px;">PEMERINTAH KABUPATEN SOLOK</h3>
        <h3 style="font-size: 18px;">KECAMATAN LEMBAH GUMANTI</h3>
        <h2 style="font-size: 22px; font-weight: bold;">KANTOR WALI NAGARI SALIMPEK</h2>
        <p style="font-size: 12px;">Jalan Alahan Panjang - Talang Babungo Km 3, Kode Pos 27371</p>
    </div>

    <div class="judul-laporan">LAPORAN REKAPITULASI PRESENSI PEGAWAI</div>
    <div class="sub-judul">Periode Bulan: {{ \Carbon\Carbon::parse($bulanDipilih . '-01')->translatedFormat('F Y') }}</div>

    <!-- 1. TABEL RINGKASAN SEMUA PEGAWAI -->
    <table>
        <thead>
            <tr>
                <th style="width: 5%;">No</th>
                <th class="text-left" style="width: 25%;">Nama Pegawai</th>
                <th class="text-left" style="width: 20%;">Jabatan</th>
                <th>Hadir</th>
                <th>Telat</th>
                <th>Izin / Sakit</th>
                <th>Dinas Luar</th>
                <th>Alpa</th>
            </tr>
        </thead>
        <tbody>
            @foreach($daftarPegawai as $index => $pegawai)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="text-left">{{ $pegawai->name }}</td>
                <td class="text-left">{{ $pegawai->jabatan }}</td>
                <td>{{ $pegawai->total_hadir }}</td>
                <td>{{ $pegawai->total_telat }}</td>
                <td>{{ $pegawai->total_izin }}</td>
                <td>{{ $pegawai->total_dl }}</td>
                <td>{{ $pegawai->total_alpa }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <br><hr><br>

    <!-- 2. TABEL DETAIL HARIAN PER PEGAWAI -->
    <div style="text-align: center; font-size: 14px; font-weight: bold; margin-bottom: 20px;">
        Rincian Presensi Harian
    </div>

    @foreach($daftarPegawai as $pegawai)
    <div class="detail-container">
        <div class="pegawai-info">
            Nama: {{ $pegawai->name }} &nbsp;&nbsp;|&nbsp;&nbsp; Jabatan: {{ $pegawai->jabatan }}
        </div>
        <table>
            <thead>
                <tr>
                    <th style="width: 20%;">Tanggal</th>
                    <th>Jam Masuk</th>
                    <th>Status Masuk</th>
                    <th>Jam Pulang</th>
                    <th>Status Pulang</th>
                    <th>Keterangan</th>
                </tr>
            </thead>
            <tbody>
                @foreach($pegawai->rekap_harian as $hari)
                <tr>
                    <td class="text-left">{{ $hari['tanggal'] }}</td>
                    <td>{{ $hari['masuk'] }}</td>
                    <td>{{ $hari['status_masuk'] }}</td>
                    <td>{{ $hari['pulang'] }}</td>
                    <td>{{ $hari['status_pulang'] }}</td>
                    <td>{{ $hari['keterangan'] }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @endforeach

    <!-- KOLOM TANDA TANGAN WALI NAGARI (DINAMIS DARI DATABASE) -->
    <div class="tanda-tangan">
        <p>Salimpek, {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y') }}</p>
        <p>Wali Nagari Salimpek</p>
        <div class="nama-ttd">{{ strtoupper($namaWali) }}</div>
        <p>NIP. {{ $nipWali }}</p>
    </div>

</body>
</html>