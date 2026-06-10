$(document).ready(function () {

    // DISABLE BUTTON SUBMIT SAAT FORM DIKIRIM
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function (e) {
            const submitButton = form.querySelector('button[type="submit"]');

            if (submitButton) {
                submitButton.disabled = true;

                submitButton.innerHTML = 'Menyimpan... <span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
            }
        });
    });

    //--------------------------------------------------------------
    // BAGIAN DATATABLES

    // 1. TAMBAHKAN CUSTOM SEARCH UNTUK FILTER TANGGAL (Berlaku Global untuk DataTables)
    $.fn.dataTable.ext.search.push(function (settings, data, dataIndex) {
        var $table = $(settings.nTable);
        var dateColIdx = $table.data('date-col'); // Ambil index kolom tanggal

        // Jika tabel ini tidak disetting untuk filter tanggal, lewati proses filter ini
        if (dateColIdx === undefined) return true;

        var startId = $table.data('date-start');
        var endId = $table.data('date-end');

        // Ambil value input tanggal dan ubah jadi objek Date()
        var minDate = $(startId).val() ? new Date($(startId).val()) : null;
        var maxDate = $(endId).val() ? new Date($(endId).val()) : null;

        // Ambil text dari kolom tanggal di tabel
        var rawColumnData = data[dateColIdx];
        // Bersihkan tag HTML (jika ada) dan ambil 10 karakter pertama (format YYYY-MM-DD)
        var cleanDateString = rawColumnData.replace(/(<([^>]+)>)/gi, "").trim().substring(0, 10);
        var rowDate = new Date(cleanDateString);

        // Logika pencarian Between
        if (
            (minDate === null && maxDate === null) ||
            (minDate === null && rowDate <= maxDate) ||
            (minDate <= rowDate && maxDate === null) ||
            (minDate <= rowDate && rowDate <= maxDate)
        ) {
            return true; // Tampilkan baris
        }
        return false; // Sembunyikan baris
    });

    // 2. INISIALISASI SELECT2 UNIVERSAl
    $('.use-select2').select2({
            // placeholder: "Pilih opsi...",
            // allowClear: true, // Memunculkan tombol X untuk menghapus pilihan
            width: '100%'     // Gunakan 100% agar lebarnya menyesuaikan form Bootstrap Anda
    });

    // 3. INISIALISASI DATATABLES
    $('.custom-datatable').each(function () {
        var $table = $(this);

        // Pengaturan Sort
        var noSortAttr = $table.data('nosort');
        var noSortTargets = noSortAttr ? noSortAttr.toString().split(',').map(Number) : [];

        var dt = $table.DataTable({
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json",
                "search": "Cari:",
                "lengthMenu": "Tampilkan _MENU_ data",
                "zeroRecords": "Data tidak ditemukan",
                "info": "Menampilkan halaman _PAGE_ dari _PAGES_",
                "infoEmpty": "Tidak ada data tersedia",
                "infoFiltered": "(difilter dari _MAX_ total data)"
            },
            "columnDefs": [{
                "orderable": false,
                "targets": noSortTargets
            }],
            "initComplete": function () {
                var $wrapper = $(dt.table().container());

                // --- A. INJECT FILTER KATEGORI (DROPDOWN) ---
                var filterTemplateId = $table.data('filter-template');
                var filterSelectId = $table.data('filter-id');
                var filterColIndex = $table.data('filter-col');

                if (filterTemplateId && filterSelectId && filterColIndex !== undefined) {
                    var $template = $(filterTemplateId);
                    if ($template.length) {
                        $wrapper.find('.dataTables_filter').prepend($template.html());
                        $template.remove();

                        $(filterSelectId).select2({
                            // placeholder: "Pilih / Cari...",
                            // allowClear: true,
                            width: '200px'
                        });

                        $(filterSelectId).on('change', function () {
                            dt.column(filterColIndex).search($(this).val()).draw();
                        });
                    }
                }

                // --- B. INJECT FILTER RANGE TANGGAL ---
                var dateTemplateId = $table.data('date-template');
                var dateStartId = $table.data('date-start');
                var dateEndId = $table.data('date-end');

                if (dateTemplateId && dateStartId && dateEndId) {
                    var $dateTemplate = $(dateTemplateId);
                    if ($dateTemplate.length) {
                        // Masukkan form tanggal ke sebelah fungsi search juga
                        $wrapper.find('.dataTables_filter').prepend($dateTemplate.html());
                        $dateTemplate.remove();

                        // Jika tanggal diubah, gambar ulang tabel (akan memicu custom search di atas)
                        $(dateStartId + ', ' + dateEndId).on('change', function () {
                            dt.draw();
                        });
                    }
                }
            }
        });
    });

    // 4. Logika row
    $('#btn-add-row').on('click', function () {
        var $tableBody = $('#table-detail tbody');
        if ($tableBody.length === 0) return;

        // Ambil baris pertama sebagai template
        var $firstRow = $tableBody.find('tr:first');
        var $newRow = $firstRow.clone();

        // Ambil index baru berdasarkan jumlah baris yang ada
        var rowIndex = $tableBody.find('tr').length;

        // Ubah attribute name
        $newRow.find(':input').each(function () {
            var name = $(this).attr('name');
            if (name) {
                $(this).attr('name', name.replace(/\[\d+\]/, '[' + rowIndex + ']'));
            }
            if ($(this).attr('type') !== 'button') {
                $(this).val('');
            }
        });

        // ---------------------------------------------------------
        // PERBAIKAN SELECT2 MULAI DARI SINI
        // ---------------------------------------------------------
        
        // 1. Hapus elemen kontainer visual Select2 hasil clone
        $newRow.find('.select2-container').remove();
        
        // 2. Bersihkan atribut bentrok dari tag <select>
        var $newSelect = $newRow.find('select');
        $newSelect.removeClass('select2-hidden-accessible')
                  .removeAttr('data-select2-id')
                  .removeAttr('tabindex')
                  .removeAttr('aria-hidden');

        // 3. [KUNCI UTAMA] Bersihkan atribut bentrok dari SEMUA tag <option>
        $newSelect.find('option').removeAttr('data-select2-id');

        // 4. Kosongkan nilai select dan paksa reset statusnya
        $newSelect.val(null).trigger('change');

        // ---------------------------------------------------------

        // Aktifkan dan fungsikan tombol hapus
        var $removeBtn = $newRow.find('.btn-remove');
        $removeBtn.prop('disabled', false);
        $removeBtn.on('click', function () {
            $(this).closest('tr').remove();
        });

        // Masukkan baris baru ke dalam tabel
        $tableBody.append($newRow);

        // Inisialisasi ulang Select2 HANYA pada select di baris baru tersebut
        $newSelect.select2({
            width: '100%'
        });
    });
});

const fileInput = document.querySelector('input[name="direktori_gambar"]');
if (fileInput) { // <-- Pengecekan keamanan
    fileInput.onchange = evt => {
        const [file] = evt.target.files;
        if (file) {
            const previewContainer = document.getElementById('new-preview-container');
            const previewImg = document.getElementById('new-preview');
            previewImg.src = URL.createObjectURL(file);
            previewContainer.style.display = 'block';
        }
    }

    // Tambahkan tombol hapus gambar dan fungsionalitasnya
    document.addEventListener('DOMContentLoaded', function () {
        const previewContainer = document.getElementById('new-preview-container');
        const previewImg = document.getElementById('new-preview');

        // Create Remove Image Button
        const removeWrapper = document.createElement('div');
        removeWrapper.className = 'mt-2';
        removeWrapper.style.display = 'none';
        removeWrapper.innerHTML = ` 
            <button type="button" id="removeImage" class="btn btn-danger mt-3">
                Hapus gambar saat ini
            </button>
        `;

        fileInput.parentNode.insertBefore(removeWrapper, fileInput.nextSibling);

        fileInput.addEventListener('change', function () {
            const [file] = this.files;
            if (file) {
                previewImg.src = URL.createObjectURL(file);
                previewContainer.style.display = 'block';
                removeWrapper.style.display = 'block';
            }
        });

        document.getElementById('removeImage').addEventListener('click', function () {
            fileInput.value = ''; // Clear file input
            previewContainer.style.display = 'none';
            removeWrapper.style.display = 'none';
        });
    });
}