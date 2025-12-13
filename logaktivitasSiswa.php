<?php

require_once('../../config.php');

redirect_if_major_upgrade_required();

require_login();

function konversiTanggal($tanggal) {
    // Mengubah tanggal dari format Y-m-d ke format d-m-Y
    $tanggal_format = date('d-m-Y', $tanggal);

    // Array bulan dalam bahasa Indonesia
    $bulan = array(
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember'
    );

    // Pisahkan tanggal menjadi hari, bulan, dan tahun
    $tanggal_pisah = explode('-', $tanggal_format);
    $hari = $tanggal_pisah[0];
    $bulan_angka = (int)$tanggal_pisah[1];
    $tahun = $tanggal_pisah[2];

    // Format tanggal dalam bahasa Indonesia
    $tanggal_indonesia = $hari . ' ' . $bulan[$bulan_angka] . ' ' . $tahun;

    return $tanggal_indonesia;
}


global $USER, $DB;
$user_id = $USER->id;
$cmid = required_param('id', PARAM_INT);
$ebelajar = $DB->get_field('ebelajar', 'id', ['coursemoduleid' => $cmid]);
$query = "
    SELECT gs.*, gp.group_number
    FROM {groupstudentproject} gs
    JOIN {groupproject} gp ON gs.groupproject = gp.id
    WHERE gs.user_id = :user_id AND gs.ebelajar = :ebelajar
";
$params = ['user_id' => $user_id, 'ebelajar' => $ebelajar];
$result = $DB->get_record_sql($query, $params);

$ebelajar_records = $DB->get_record('ebelajar', ['coursemoduleid' => $cmid]);
$groupproject_records = $DB->get_records('groupproject', ['ebelajar' => $ebelajar]);
$step3_schedule_image = $DB->get_field('ebelajar', 'step3_schedule_image', ['coursemoduleid' => $cmid]);
$step3_schedule_file = $DB->get_field('ebelajar', 'step3_schedule_file', ['coursemoduleid' => $cmid]);

$project_records = $DB->get_records('project', ['ebelajar' => $ebelajar]);
$filtered_records = [];

foreach ($project_records as $record) {
    if ($result && $record->group_project != $result->groupproject) {
        $groups_project = $DB->get_record('groupproject', ['id' => $record->group_project], 'group_number');
        
        if ($groups_project) {
            $record->group_number = $groups_project->group_number;
        }
        
        $filtered_records[] = $record;
    }
}

if ($result) {
    $group_project = $result->groupproject;
    $project_data = $DB->get_record('project', ['status_step5' => "Selesai", 'group_project' => $group_project]);
    $step = $DB->get_record('project', ['group_project' => $group_project]);
    $step1_status = $DB->get_field('project', 'status_step1', ['group_project' => $group_project]);
    $step2_status = $DB->get_field('project', 'status_step2', ['group_project' => $group_project]);
    $step3_status = $DB->get_field('project', 'status_step3', ['group_project' => $group_project]);
    $step4_status = $DB->get_field('project', 'status_step4', ['group_project' => $group_project]);
    $step5_status = $DB->get_field('project', 'status_step5', ['group_project' => $group_project]);
    $step6_status = $DB->get_field('project', 'status_step6', ['group_project' => $group_project]);
    
    // Fetch indicators for display
    $indicators = [];
    if ($step && isset($step->id)) {
        $indicators = $DB->get_records('project_indicators', 
            ['project_id' => $step->id], 
            'created_at ASC'
        );
    }
} else {
    $project_data = null;
    $group_project = null;
    $step1_status = null;
    $step2_status = null;
    $step3_status = null;
    $step4_status = null;
    $step5_status = null;
    $step6_status = null;
}

$query2 = "
    SELECT ar.*, u.username, u.firstname, u.lastname
    FROM {activity_report} ar
    JOIN {user} u ON ar.user_id = u.id
    WHERE ar.groupproject = :groupproject
    ORDER BY ar.date_activity ASC
";
$params2 = ['groupproject' => $group_project];
$results2 = $DB->get_records_sql($query2, $params2);

?>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css" rel="stylesheet">

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>
    
    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>

    <style>

        :root {
                --custom-blue: #bed4d1;
                --custom-green: #5a9f68;
                --custom-red: #ff5757;
            }
        .navskuy {
            background-color: var(--custom-green); 
            border: 4px solid var(--custom-blue);
            color: #ffffff;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .logo {
            display: inline-block;
        }
    </style>

  <script>
      $(document).ready(function() {
          $('#table').DataTable();
      });
  </script>

    <script>
    $(document).ready(function() {
        $('#btnSimpan').click(function() {
            var formData = $('#formTambahData').serialize();
            $.ajax({
                url: 'formtambahData.php',
                type: 'POST',
                data: formData,
                success: function(response) {
                    console.log(response);                  
                    // Tampilkan SweetAlert
                    Swal.fire({
                        icon: 'success',
                        title: 'Data berhasil disimpan!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        location.reload();
                    });
                }
            });
        });
    });
    </script>

    <script>
    $(document).ready(function() {
        $('#btnUpdated').click(function() {
            var formData = $('#formUbahData').serialize();
            $.ajax({
                url: 'formeditData.php',
                type: 'POST',
                data: formData,
                success: function(response) {
                    console.log(response);   
                    Swal.fire({
                        icon: 'success',
                        title: 'Data berhasil disimpan!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        $('#formUbahData')[0].reset();
                        $('#modalDataDiriUbahData').modal('hide');
                        $('#dataContainer').load(location.href + ' #dataContainer');
                        $('#dataButtonLoad').load(location.href + ' #dataButtonLoad');
                        $('#editLoad').load(location.href + ' #editLoad');
                        $('#dataProjectContainer').load(location.href + ' #dataProjectContainer');
                    });
                }
            });
        });
    });
    </script>

    <script>
    $(document).ready(function() {
        $('#btnSimpanActivity').click(function() {
            var formData = new FormData($('#formTambahDataActivity')[0]);
            var nama_kegiatan = $('[name="nama_kegiatan"]').val();
            var uraian_kegiatan = $('[name="uraian_kegiatan"]').val();
            var tanggal_kegiatan = $('[name="tanggal_kegiatan"]').val();
            var bukti_kegiatan = $('[name="bukti_kegiatan"]').prop('files')[0];

            if (!nama_kegiatan || !uraian_kegiatan || !tanggal_kegiatan) {
                alert("Harap lengkapi semua bidang.");
                return;
            }

            if (!bukti_kegiatan) {
                alert("Harap unggah bukti kegiatan.");
                return;
            }
            
            $.ajax({
                url: 'formtambahDataActivity.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    console.log(response);                   
                    Swal.fire({
                        icon: 'success',
                        title: 'Data berhasil disimpan!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        // Setelah SweetAlert ditutup, tutup modal
                        $('#formTambahDataActivity')[0].reset();
                        $('#modalTambahKegiatan').modal('hide');
                        $('#table').load(location.href + ' #table');
                        location.reload();
                    });
                },
                error: function(xhr, status, error) {
                    console.log("Terjadi kesalahan: " + error);
                }
            });
        });
    });
    </script>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var lihatButtons = document.querySelectorAll('.lihat-btn');
        
        lihatButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                var imageUrl = this.getAttribute('data-foto-url');
                var modalContent = document.querySelector('#modalLihatFoto .modal-body');
                modalContent.innerHTML = '<img src="' + imageUrl + '" class="img-fluid mx-auto d-block" alt="Foto Kegiatan">';
            });
        });
    });
    </script>
    
    <script>
    function hapusKegiatan(id) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Anda tidak akan dapat mengembalikan ini!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Ya, hapus saja!'
        }).then((result) => {
            if (result.isConfirmed) {
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "hapus_kegiatan.php", true);
                xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === 4 && xhr.status === 200) {
                        // Tampilkan pesan berhasil
                        Swal.fire(
                            'Deleted!',
                            'Kegiatan telah berhasil dihapus.',
                            'success'
                        ).then(() => {
                            location.reload();
                        });
                    }
                };
                xhr.send("id=" + id);
            }
        });
    }
    </script>

    <script>
    $(document).ready(function() {
        $('#btnSimpanProject').click(function() {
            var formData = new FormData($('#formTambahDataProject')[0]);
            var title_project = $('[name="title_project"]').val();
            var description_project = $('[name="description_project"]').val();
            var file_project = $('[name="file_project"]').prop('files')[0];

            if (!title_project || !description_project) {
                alert("Harap lengkapi semua bidang.");
                return;
            }
            
            $.ajax({
                url: 'formtambahDataProject.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    console.log(response);                   
                    Swal.fire({
                        icon: 'success',
                        title: 'Data berhasil disimpan!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        // Setelah SweetAlert ditutup, tutup modal
                        $('#formTambahDataProject')[0].reset();
                        $('#modalTambahProject').modal('hide');
                        location.reload();
                    });
                },
                error: function(xhr, status, error) {
                    console.log("Terjadi kesalahan: " + error);
                }
            });
        });
    });
    </script>

    <script>
    $(document).ready(function() {
        $('#btnUpdateProject').click(function() {
            var form = $(this).closest('.modal').find('form');

            var formData = new FormData(form[0]);
            
            $.ajax({
                url: 'formeditDataProject.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    console.log(response);                   
                    Swal.fire({
                        icon: 'success',
                        title: 'Data berhasil disimpan!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        // Setelah SweetAlert ditutup, tutup modal
                        $('#formEditDataProject')[0].reset();
                        $('#modalEditProject').modal('hide');
                        location.reload();
                    });
                },
                error: function(xhr, status, error) {
                    console.log("Terjadi kesalahan: " + error);
                }
            });
        });
    });
    </script>

    <script>
    $(document).ready(function() {
        document.querySelectorAll('.btnedit').forEach(button => {
            button.addEventListener('click', function() {
                const rowId = this.getAttribute('data-id');
                const modalId = 'modalEditKegiatan_' + rowId;

                const namaKegiatan = this.getAttribute('data-nama');
                const uraianKegiatan = this.getAttribute('data-uraian');
                const tanggalKegiatan = this.getAttribute('data-tanggal'); 
                const oldFilePath = this.getAttribute('data-file');
                const groupproject = this.getAttribute('data-groupproject');

                let modalHTML = `
                    <div class="modal fade" id="${modalId}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                                    <h5 class="modal-title" id="exampleModalLabel">Edit Kegiatan</h5>
                                </div>
                                <div class="modal-body">
                                    <form id="formEditDataActivity" method="POST" enctype="multipart/form-data" class="p-4 border rounded bg-light">
                                        <input type="hidden" name="id" value="${rowId}">
                                        <input type="hidden" name="old_file_path" value="${oldFilePath}">
                                        <input type="hidden" name="group_project" value="${groupproject}>">

                                        <!-- Nama Kegiatan -->
                                        <div class="mb-3">
                                            <label for="nama_kegiatan" class="form-label">Nama Kegiatan</label>
                                            <input type="text" id="nama_kegiatan" name="nama_kegiatan" 
                                                class="form-control rounded-2 px-3 py-2" 
                                                style="color:#000000;" 
                                                placeholder="Tambah Kegiatan" 
                                                value="${namaKegiatan}" required>
                                        </div>

                                        <!-- Uraian Kegiatan -->
                                        <div class="mb-3">
                                            <label for="uraian_kegiatan" class="form-label">Uraian Kegiatan</label>
                                            <textarea id="uraian_kegiatan" name="uraian_kegiatan" 
                                                class="form-control shadow-sm rounded-lg p-3" 
                                                style="color:#000000; border: 1px solid #000000;" 
                                                placeholder="Masukkan Uraian Kegiatan" 
                                                rows="4" required>${uraianKegiatan}</textarea>
                                        </div>

                                        <!-- Tanggal Kegiatan -->
                                        <div class="mb-3">
                                            <label for="tanggal_kegiatan" class="form-label">Tanggal Kegiatan</label>
                                            <input type="date" id="tanggal_kegiatan" name="tanggal_kegiatan" 
                                                class="form-control rounded-2 px-3 py-2" 
                                                style="color:#000000;" 
                                                value="${tanggalKegiatan}" required>
                                        </div>

                                        <!-- Bukti Kegiatan -->
                                        <div class="mb-3">
                                            <label for="bukti_kegiatan" class="form-label">Bukti Kegiatan</label>
                                            <input type="file" id="bukti_kegiatan" name="bukti_kegiatan" 
                                                class="form-control rounded-2 px-3 py-2" 
                                                style="color:#000000;" 
                                                accept=".jpg, .jpeg, .png, .gif">
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button id="btnUpdateActivity" type="button" class="btn rounded-pill w-25 btnUpdateActivity" style="background-color: var(--custom-green); color:#ffffff">Simpan</button>
                                    <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                document.body.insertAdjacentHTML('beforeend', modalHTML);

                const modalElement = new bootstrap.Modal(document.getElementById(modalId));
                modalElement.show();

                $('.btnUpdateActivity').click(function() {
                    var form = $(this).closest('.modal').find('form');

                    var formData = new FormData(form[0]);

                    $.ajax({
                        url: 'formeditDataActivity.php',
                        type: 'POST',
                        data: formData,
                        contentType: false,
                        processData: false,
                        success: function(response) {
                            console.log(response);               
                            Swal.fire({
                                icon: 'success',
                                title: 'Data berhasil disimpan!',
                                showConfirmButton: false,
                                timer: 1500
                            }).then(() => {
                                $(this).closest('.modal').modal('hide');
                                $('#table').load(location.href + ' #table');
                                location.reload();
                            });
                        },
                        error: function(xhr, status, error) {
                            console.log("Terjadi kesalahan: " + error);
                        }
                    });
                });

                document.getElementById(modalId).addEventListener('hidden.bs.modal', function () {
                    this.remove();
                });
            });
        });
    });
    </script>

    <script>
    let indicatorCount = 0;

    // Function to add new indicator row - Make it global
    window.addIndicatorRow = function() {
        const rowId = indicatorCount++;
        const row = `
            <tr id="row_${rowId}" class="indicator-row">
                <td>
                    <input type="text" class="form-control ind-name" data-row="${rowId}" 
                           placeholder="Contoh: Limbah Pabrik" required>
                </td>
                <td>
                    <textarea class="form-control ind-analysis" data-row="${rowId}" 
                              placeholder="Jelaskan analisis masalah..." rows="2" required></textarea>
                </td>
                <td>
                    <input type="text" class="form-control mb-1 ind-ref" data-row="${rowId}" placeholder="Referensi 1 (URL/Sumber)" required>
                    <input type="text" class="form-control mb-1 ind-ref" data-row="${rowId}" placeholder="Referensi 2 (URL/Sumber)" required>
                    <input type="text" class="form-control ind-ref" data-row="${rowId}" placeholder="Referensi 3 (URL/Sumber)" required>
                </td>
                <td>
                    <select class="form-select ind-valid" data-row="${rowId}" onchange="toggleGreyout(${rowId}, this.value)">
                        <option value="1">Terbukti</option>
                        <option value="0">Tidak Terbukti</option>
                    </select>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeIndicatorRow(${rowId})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#indicatorRows').append(row);
    }

    // Function to remove indicator row - Make it global
    window.removeIndicatorRow = function(rowId) {
        $('#row_' + rowId).remove();
    }

    // Function to toggle greyout - Make it global
    window.toggleGreyout = function(rowId, isValid) {
        const row = $('#row_' + rowId);
        if (isValid == '0') {
            row.addClass('table-secondary text-muted');
        } else {
            row.removeClass('table-secondary text-muted');
        }
    }

    // Function to collect indicators data
    function collectIndicatorsData() {
        const indicators = [];
        $('.indicator-row').each(function() {
            const rowId = $(this).find('.ind-name').data('row');
            const name = $(this).find('.ind-name').val().trim();
            const analysis = $(this).find('.ind-analysis').val().trim();
            const refs = [];
            $(this).find('.ind-ref').each(function() {
                const ref = $(this).val().trim();
                if (ref) refs.push(ref);
            });
            const isValid = $(this).find('.ind-valid').val();
            
            if (name && analysis && refs.length >= 3) {
                indicators.push({
                    name: name,
                    analysis: analysis,
                    references: JSON.stringify(refs),
                    is_valid: isValid
                });
            }
        });
        return indicators;
    }

    // Use event delegation for dynamically added buttons
    $(document).on('click', '#btnAddRow', function() {
        addIndicatorRow();
    });

    // Step 1 Submit
    $(document).on('click', '#btnSimpanStep1', function() {
        // Validate formulation
        const step1_formulation = $('[name="step1_formulation"]').val();
        if (!step1_formulation) {
            alert("Harap isi rumusan masalah.");
            return;
        }

        // Collect indicators
        const indicators = collectIndicatorsData();
        
        // Validate minimum references
        let valid = true;
        $('.indicator-row').each(function() {
            const refs = [];
            $(this).find('.ind-ref').each(function() {
                const ref = $(this).val().trim();
                if (ref) refs.push(ref);
            });
            if (refs.length < 3) {
                valid = false;
                alert("Setiap indikator harus memiliki minimal 3 referensi!");
                return false;
            }
        });
        
        if (!valid) return;

        // Set indicators data
        $('#indicatorsData').val(JSON.stringify(indicators));

        // Submit via AJAX
        var formData = $('#formTambahDataStep1').serialize();
        $.ajax({
            url: 'formtambahDataStep1.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                console.log(response);
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Data berhasil disimpan!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        $('#formTambahDataStep1')[0].reset();
                        $('#modalTambahStep1').modal('hide');
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: response.message || 'Terjadi kesalahan'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.log("Terjadi kesalahan: " + error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat menyimpan data'
                });
            }
        });
    });
    </script>

    <script>
    $(document).ready(function() {
        $('#btnSimpanStep2').click(function() {
            var formData = new FormData($('#formTambahDataStep2')[0]);
            var step2_pondation = $('[name="step2_pondation"]').prop('files')[0];

            if (!step2_pondation) {
                alert("Harap lengkapi semua bidang.");
                return;
            }
            
            $.ajax({
                url: 'formtambahDataStep2.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    console.log(response);                   
                    Swal.fire({
                        icon: 'success',
                        title: 'Data berhasil disimpan!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        // Setelah SweetAlert ditutup, tutup modal
                        $('#formTambahDataStep2')[0].reset();
                        $('#modalTambahStep2').modal('hide');
                        location.reload();
                    });
                },
                error: function(xhr, status, error) {
                    console.log("Terjadi kesalahan: " + error);
                }
            });
        });
    });
    </script>

    <script>
    $(document).ready(function() {
        $('#btnSimpanStep6').click(function() {
            var formData = new FormData($('#formTambahDataStep6')[0]);
            var evaluation = $('[name="evaluation"]').val();

            if (!evaluation) {
                alert("Harap lengkapi semua bidang.");
                return;
            }
            
            $.ajax({
                url: 'formtambahDataStep6.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    console.log(response);                   
                    Swal.fire({
                        icon: 'success',
                        title: 'Data berhasil disimpan!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        // Setelah SweetAlert ditutup, tutup modal
                        $('#formTambahDataStep6')[0].reset();
                        $('#modalTambahStep6').modal('hide');
                        location.reload();
                    });
                },
                error: function(xhr, status, error) {
                    console.log("Terjadi kesalahan: " + error);
                }
            });
        });
    });
    </script>

    <script>
    let indicatorCountEdit = 0;
    
    // Function to add indicator row for edit mode - Make it global
    window.addIndicatorRowEdit = function(name = '', analysis = '', refs = [], isValid = 1) {
        const rowId = indicatorCountEdit++;
        const refsArray = Array.isArray(refs) ? refs : [];
        const ref1 = refsArray[0] || '';
        const ref2 = refsArray[1] || '';
        const ref3 = refsArray[2] || '';
        
        const row = `
            <tr id="row_edit_${rowId}" class="indicator-row-edit ${isValid == 0 ? 'table-secondary text-muted' : ''}">
                <td>
                    <input type="text" class="form-control ind-name-edit" data-row="${rowId}" 
                           placeholder="Contoh: Limbah Pabrik" value="${name}" required>
                </td>
                <td>
                    <textarea class="form-control ind-analysis-edit" data-row="${rowId}" 
                              placeholder="Jelaskan analisis masalah..." rows="2" required>${analysis}</textarea>
                </td>
                <td>
                    <input type="text" class="form-control mb-1 ind-ref-edit" data-row="${rowId}" 
                           placeholder="Referensi 1 (URL/Sumber)" value="${ref1}" required>
                    <input type="text" class="form-control mb-1 ind-ref-edit" data-row="${rowId}" 
                           placeholder="Referensi 2 (URL/Sumber)" value="${ref2}" required>
                    <input type="text" class="form-control ind-ref-edit" data-row="${rowId}" 
                           placeholder="Referensi 3 (URL/Sumber)" value="${ref3}" required>
                </td>
                <td>
                    <select class="form-select ind-valid-edit" data-row="${rowId}" 
                            onchange="toggleGreyoutEdit(${rowId}, this.value)">
                        <option value="1" ${isValid == 1 ? 'selected' : ''}>Terbukti</option>
                        <option value="0" ${isValid == 0 ? 'selected' : ''}>Tidak Terbukti</option>
                    </select>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-danger btn-sm" onclick="removeIndicatorRowEdit(${rowId})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
        $('#indicatorRowsEdit').append(row);
    }
    
    window.removeIndicatorRowEdit = function(rowId) {
        $('#row_edit_' + rowId).remove();
    }
    
    window.toggleGreyoutEdit = function(rowId, isValid) {
        const row = $('#row_edit_' + rowId);
        if (isValid == '0') {
            row.addClass('table-secondary text-muted');
        } else {
            row.removeClass('table-secondary text-muted');
        }
    }
    
    function collectIndicatorsDataEdit() {
        const indicators = [];
        $('.indicator-row-edit').each(function() {
            const rowId = $(this).find('.ind-name-edit').data('row');
            const name = $(this).find('.ind-name-edit').val().trim();
            const analysis = $(this).find('.ind-analysis-edit').val().trim();
            const refs = [];
            $(this).find('.ind-ref-edit').each(function() {
                const ref = $(this).val().trim();
                if (ref) refs.push(ref);
            });
            const isValid = $(this).find('.ind-valid-edit').val();
            
            if (name && analysis && refs.length >= 3) {
                indicators.push({
                    name: name,
                    analysis: analysis,
                    references: JSON.stringify(refs),
                    is_valid: isValid
                });
            }
        });
        return indicators;
    }
    
    // Load existing indicators when edit modal opens
    $(document).on('show.bs.modal', '#modalEditStep1', function() {
        // Clear existing rows
        $('#indicatorRowsEdit').empty();
        indicatorCountEdit = 0;
        
        // Fetch existing indicators via AJAX
        const groupProject = $('input[name="group_project"]').val();
        $.ajax({
            url: 'get_indicators.php',
            type: 'GET',
            data: { group_project: groupProject },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.indicators.length > 0) {
                    response.indicators.forEach(function(ind) {
                        const refs = JSON.parse(ind.references);
                        addIndicatorRowEdit(ind.indicator_name, ind.analysis, refs, ind.is_valid);
                    });
                }
            }
        });
    });
    
    // Use event delegation for edit button
    $(document).on('click', '#btnAddRowEdit', function() {
        addIndicatorRowEdit();
    });
    
    // Edit Step 1 Submit using event delegation
    $(document).on('click', '#btnUpdatedStep1', function() {
        const step1_formulation = $('#step1_formulation_edit').val();
        if (!step1_formulation) {
            alert("Harap isi rumusan masalah.");
            return;
        }

        const indicators = collectIndicatorsDataEdit();
        
        let valid = true;
        $('.indicator-row-edit').each(function() {
            const refs = [];
            $(this).find('.ind-ref-edit').each(function() {
                const ref = $(this).val().trim();
                if (ref) refs.push(ref);
            });
            if (refs.length < 3) {
                valid = false;
                alert("Setiap indikator harus memiliki minimal 3 referensi!");
                return false;
            }
        });
        
        if (!valid) return;

        $('#indicatorsDataEdit').val(JSON.stringify(indicators));

        var formData = $('#formUbahStep1').serialize();
        $.ajax({
            url: 'formeditDataStep1.php',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                console.log(response);   
                if (response.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Data berhasil disimpan!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        $('#formUbahStep1')[0].reset();
                        $('#modalEditStep1').modal('hide');
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Gagal!',
                        text: response.message || 'Terjadi kesalahan'
                    });
                }
            },
            error: function(xhr, status, error) {
                console.log("Terjadi kesalahan: " + error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error!',
                    text: 'Terjadi kesalahan saat menyimpan data'
                });
            }
        });
    });
    </script>

    <script>
    $(document).ready(function() {
        $('#btnUpdatedStep2').click(function() {
            var formData = new FormData($('#formUbahStep2')[0]);
            $.ajax({
                url: 'formeditDataStep2.php',
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    console.log(response);   
                    Swal.fire({
                        icon: 'success',
                        title: 'Data berhasil disimpan!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        $('#formUbahStep2')[0].reset();
                        $('#modalEditStep2').modal('hide');
                        location.reload();
                    });
                }
            });
        });
    });
    </script>

    <script>
    $(document).ready(function() {
        $('#btnUpdatedStep6').click(function() {
            var formData = $('#formUbahStep6').serialize();
            $.ajax({
                url: 'formeditDataStep6.php',
                type: 'POST',
                data: formData,
                success: function(response) {
                    console.log(response);   
                    Swal.fire({
                        icon: 'success',
                        title: 'Data berhasil disimpan!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(() => {
                        $('#formUbahStep6')[0].reset();
                        $('#modalEditStep6').modal('hide');
                        location.reload();
                    });
                }
            });
        });
    });
    </script>

    <script>
    $(document).ready(function() {
        $('#btnSubmitLihat').click(function() {
            var cmid = $("input[name='cmid']").val();
            console.log("cmid:", cmid);

            const kelompokId = $('#lihat_kelompok').val(); 
            console.log("kelompokId:", kelompokId); 

            if (kelompokId) {
                lihatDetail(cmid, kelompokId);
                $('#formLihatKelompok')[0].reset();
                $('#modalLihatKelompok').modal('hide');
            } else {
                alert("Silakan pilih kelompok terlebih dahulu.");
            }
        });
    });
    </script>

    <script>
    function lihatDetail(id, kelompokId) {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "logaktivitasKelompokSiswa.php?id=" + id + "&kelompok=" + kelompokId, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                var mainDiv = document.querySelector('div[role="main"]');
                if (mainDiv) {
                    mainDiv.innerHTML = xhr.responseText;
                    $('#table').DataTable();
                }
            }
        };
        xhr.send();
    }

    function kembali() {
        location.reload();
    }
    </script>

    


  <nav class="container navbar navskuy navbar-expand-md navbar-light px-2">
    <div class="container-fluid px-md-5">
        <div class="logo">
            <!-- <img  src="images/logo_elearning.png" alt="Logo e-legbook" style="width: 60px; height: 150px;"> -->
            <h3 class="navbar-brand text-white fw-bolder">Activityku</h3>
        </div>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                </li>
            </ul>
            <div class="d-flex flex-column flex-md-row gap-0 gap-md-3">
                <div id="dataButtonLoad">
                <?php
                    if ($result) {
                        echo '<button class="btn mr-md-2 mb-2 mb-md-0 rounded-pill px-4" style="background-color: var(--custom-blue); color:#000000" data-bs-toggle="modal" data-bs-target="#modalDataDiriUbahData"><i class="fas fa-plus"></i> Ubah Data Diri</button>';
                    } else {
                        echo '<button class="btn mr-md-2 mb-2 mb-md-0 rounded-pill px-4" style="background-color: var(--custom-red); color:#FFFFFF" data-bs-toggle="modal" data-bs-target="#modalDataDiri"><i class="fas fa-plus"></i> Tambah Data Diri</button>';
                    }
                ?>   
                </div>   
            </div>
        </div>
    </div>
  </nav>

  <div class="modal fade" id="modalDataDiri" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                <h5 class="modal-title" id="exampleModalLabel">Tambah Data Diri</h5>
              </div>
              <div class="modal-body">
                <form id="formTambahData" method="POST" class="p-3 border rounded bg-light">
                    <input type="hidden" name="id" value="<?php echo $cmid; ?>">
                    <div class="mb-3">
                        <label for="nama_siswa" class="form-label">Nama Siswa</label>
                        <input type="text" id="nama_siswa" name="nama_siswa" placeholder="Nama Siswa" 
                            class="form-control rounded-2 px-3" 
                            style="color:#000000;" 
                            value="<?php 
                            $firstname = isset($_SESSION["USER"]->firstname) ? $_SESSION["USER"]->firstname : "";
                            $lastname = isset($_SESSION["USER"]->lastname) ? $_SESSION["USER"]->lastname : "";
                            echo htmlspecialchars(trim($firstname) . " " . trim($lastname));  
                            ?>" readonly required>
                    </div>

                    <div class="mb-3 d-flex flex-column">
                        <label for="no_kelompok" class="form-label">Nomor Kelompok</label>
                        <select id="no_kelompok" name="no_kelompok" 
                            class="form-select rounded-2 px-3 py-2 mt-2" 
                            style="color:#000000;" required>
                            <option value="">Pilih No Kelompok</option>
                            <?php
                            foreach ($groupproject_records as $groupproject) {
                                echo '<option value="' . $groupproject->id . '">Kelompok ' . $groupproject->group_number . '</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="jobdesk" class="form-label">Jobdesk</label>
                        <input type="text" id="jobdesk" name="jobdesk" placeholder="Jobdesk" 
                            class="form-control rounded-2 px-3" 
                            style="color:#000000;" required>
                    </div>
                </form>
              </div>
              <div class="modal-footer">
                  <button id="btnSimpan" type="button" class="btn rounded-pill w-25" style="background-color: var(--custom-green); color:#000000">Simpan</button>
                  <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
  </div>

  <div class="modal fade" id="modalDataDiriUbahData" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                  <h5 class="modal-title" id="exampleModalLabel">Tambah Data Diri</h5>
              </div>
              <div class="modal-body">

                <div id="editLoad">
                    <form id="formUbahData" method="POST" class="p-3 border rounded bg-light">
                        <input type="hidden" name="id" value="<?php echo $result->id; ?>">

                        <div class="mb-3">
                            <label for="nama_siswa" class="form-label">Nama Siswa</label>
                            <input type="text" id="nama_siswa" name="nama_siswa" placeholder="Nama Siswa" 
                                class="form-control rounded-2 px-3" 
                                style="color:#000000;" 
                                value="<?php echo $result->name_student; ?>" readonly required>
                        </div>

                        <div class="mb-3 d-flex flex-column">
                            <label for="no_kelompok" class="form-label">Nomor Kelompok</label>
                            <select id="no_kelompok" name="no_kelompok" 
                                class="form-select rounded-2 px-3 py-2 mt-2" 
                                style="color:#000000;" required>
                                <?php
                                foreach ($groupproject_records as $groupproject) {
                                    $selected = ($i == $result->groupproject) ? 'selected' : '';
                                    echo '<option value="' . $groupproject->id . '" ' . $selected . '>Kelompok ' . $groupproject->group_number . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="jobdesk" class="form-label">Jobdesk</label>
                            <input type="text" id="jobdesk" name="jobdesk" placeholder="Jobdesk" 
                                class="form-control rounded-2 px-3" 
                                style="color:#000000;" 
                                value="<?php echo $result->jobdesk; ?>" required>
                        </div>
                    </form>
                </div>
                
              </div>
              <div class="modal-footer">
                  <button id="btnUpdated" type="button" class="btn rounded-pill w-25" style="background-color: var(--custom-green); color:#ffffff">Simpan</button>
                  <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
  </div>

    <div class="container mx-auto p-3">
        <div class="row align-items-center border rounded-2 p-3" style="background-color: var(--custom-green);">
            <div class="col-lg-6 col-12 text-center text-lg-start mb-4 mb-lg-0">
                <h3 class="fw-bolder text-white">Selamat Datang!</h3>
                <p class="text-white">Silahkan melengkapi kegiatan anda selama melakukan aktivitas pembelajaran secara kelompok.</p>
            </div>
            <!-- Kolom gambar -->
            <div class="col-lg-6 col-12 text-center">
                <img src="assets/guru-awal(2).svg" class="img-fluid" alt="Ilustrasi">
            </div>
        </div>
    </div>

    <div class="container mx-auto p-3">
        <div class="row align-items-center border rounded-2 p-3" style="background-color: var(--custom-green);">
            <!-- Kolom gambar -->
            <div class="col-lg-6 col-12 text-center">
                <img src="assets/guru-awal(4).svg" class="img-fluid" alt="Ilustrasi">
            </div>
            <div class="col-lg-6 col-12 text-center text-lg-start mb-4 mb-lg-0">
                <h3 class="fw-bolder text-white">Perlu melihat kelompok lain?</h3>
                <p class="text-white">Silahkan pilih tombol dibawah ini untuk melihat kelompok lain</p>
                <?php if ($result == null): ?>
                    <div class="d-flex justify-content-lg-start justify-content-center mb-2">
                        <button class="btn text-black bg-secondary" disabled><i class="fas fa-xmark"></i> Tambahkan Data Diri dulu</button>
                    </div>
                <?php else: ?>
                    <div class="d-flex justify-content-lg-start justify-content-center mb-2">
                        <button class="btn text-white" style="background-color: var(--custom-red);" data-bs-toggle="modal" data-bs-target="#modalLihatKelompok"><i class="fas fa-eye"></i> Lihat Kelompok Lain</button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="container mx-auto p-3">   
        <div id="dataContainer">
            <?php
                if ($result) {
                    echo '
                    <div class="d-flex justify-content-center gap-2">
                        <div class="border rounded-2 px-3 py-2 w-75 text-center text-white" style="background-color: var(--custom-green);">' . $result->name_student . '</div>
                        <div class="border rounded-2 px-3 py-2 w-75 text-center text-white" style="background-color: var(--custom-green);">Kelompok ' .$result->group_number . '</div>
                    </div>
                    <div class="d-flex justify-content-center gap-2 mt-3">
                        <div class="border rounded-2 px-3 py-2 w-75 text-center text-white" style="background-color: var(--custom-green);">Jobdesk : ' . $result->jobdesk . '</div>
                    </div>';            
                } else {
                    echo '
                    <div class="d-flex justify-content-center gap-2">
                        <div class="border rounded-2 px-3 py-2 w-75 text-center" style="background-color: var(--custom-green);">Belum menambahkan data</div>
                        <div class="border rounded-2 px-3 py-2 w-75 text-center" style="background-color: var(--custom-green);">Belum gabung kelompok</div>
                    </div>
                    <div class="d-flex justify-content-center gap-2 mt-3">
                        <div class="border rounded-2 px-3 py-2 w-75 text-center" style="background-color: var(--custom-green);">Belum ada jobdesk</div>
                    </div>
                    ';
                }
            ?>
        </div>  
    </div>


    <?php if ($result == null): ?>
        <h1 class="text-center">Silahkan gabung kelompok terlebih dahulu!</h1>
    <?php else: ?>
        <div class="container mx-auto p-3" id="dataStep1">
            <div class="row">
                <div class="col-12">
                    <?php if ($step1_status == "Selesai"): ?>
                        <h3>Tahap 1</h3>
                        <div class="d-flex justify-content-end mb-2">
                            <?php if ($result && $result->is_leader): ?>
                                <button class="btn text-white" style="background-color: var(--custom-red);" data-bs-toggle="modal" data-bs-target="#modalEditStep1">
                                    <i class="fas fa-edit"></i> Edit Rumusan masalah
                                </button>
                            <?php else: ?>
                                <span class="badge bg-secondary p-2">
                                    <i class="fas fa-lock"></i> Hanya ketua yang dapat mengedit
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="card">
                            <div class="card-header text-white" style="background-color: var(--custom-green);">
                                <h4><i class="fas fa-lightbulb"></i> Penentuan Pertanyaan Mendasar</h4>
                            </div>
                            <div class="card-body" style="background-color: var(--custom-blue);">
                                <!-- Scenario Display -->
                                <div class="mb-4">
                                    <h5><i class="fas fa-book-open"></i> Skenario:</h5>
                                    <div class="p-3 bg-white rounded">
                                        <?php 
                                        $scenario = !empty($ebelajar_records->teacher_scenario) 
                                            ? $ebelajar_records->teacher_scenario 
                                            : $ebelajar_records->case_study;
                                        echo nl2br(htmlspecialchars($scenario)); 
                                        ?>
                                    </div>
                                </div>

                                <!-- Problem Formulation -->
                                <div class="mb-4">
                                    <h5><i class="fas fa-question-circle"></i> Rumusan Masalah:</h5>
                                    <div class="p-3 bg-white rounded">
                                        <?php 
                                        if (!empty($step->step1_formulation)) {
                                            echo nl2br(htmlspecialchars($step->step1_formulation));
                                        } else {
                                            echo '<span class="badge bg-warning text-dark">Belum diisi</span>';
                                        }
                                        ?>
                                    </div>
                                </div>

                                <!-- Indicators Table -->
                                <?php if (!empty($indicators)): ?>
                                <div class="mb-3">
                                    <h5><i class="fas fa-list-check"></i> Indikator & Analisis (<?php echo count($indicators); ?>):</h5>
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-hover bg-white">
                                            <thead class="table-light">
                                                <tr>
                                                    <th style="width: 5%" class="text-center">#</th>
                                                    <th style="width: 25%">Indikator</th>
                                                    <th style="width: 35%">Analisis</th>
                                                    <th style="width: 25%">Referensi</th>
                                                    <th style="width: 10%" class="text-center">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                            <?php 
                                            $no = 1;
                                            foreach ($indicators as $ind): 
                                                $refs = json_decode($ind->references, true);
                                                $is_invalid = ($ind->is_valid == 0);
                                                $row_class = $is_invalid ? 'table-secondary text-muted' : '';
                                            ?>
                                                <tr class="<?php echo $row_class; ?>">
                                                    <td class="text-center fw-bold"><?php echo $no++; ?></td>
                                                    <td class="<?php echo $is_invalid ? 'text-decoration-line-through' : ''; ?>">
                                                        <?php echo htmlspecialchars($ind->indicator_name); ?>
                                                    </td>
                                                    <td class="<?php echo $is_invalid ? 'text-decoration-line-through' : ''; ?>">
                                                        <?php echo nl2br(htmlspecialchars($ind->analysis)); ?>
                                                    </td>
                                                    <td>
                                                        <?php if (is_array($refs) && count($refs) > 0): ?>
                                                            <ol class="mb-0 ps-3 small">
                                                            <?php foreach ($refs as $ref): ?>
                                                                <li class="mb-1">
                                                                    <a href="<?php echo htmlspecialchars($ref); ?>" target="_blank" class="text-decoration-none">
                                                                        <?php echo htmlspecialchars(strlen($ref) > 50 ? substr($ref, 0, 50) . '...' : $ref); ?>
                                                                        <i class="fas fa-external-link-alt fa-xs"></i>
                                                                    </a>
                                                                </li>
                                                            <?php endforeach; ?>
                                                            </ol>
                                                            <?php if (count($refs) < 3): ?>
                                                                <small class="text-danger d-block mt-1">
                                                                    <i class="fas fa-exclamation-triangle"></i> Kurang dari 3 referensi
                                                                </small>
                                                            <?php endif; ?>
                                                        <?php else: ?>
                                                            <span class="badge bg-warning">Tidak ada referensi</span>
                                                        <?php endif; ?>
                                                    </td>
                                                    <td class="text-center">
                                                        <?php if ($is_invalid): ?>
                                                            <span class="badge bg-secondary">
                                                                <i class="fas fa-times-circle"></i> Tidak Terbukti
                                                            </span>
                                                        <?php else: ?>
                                                            <span class="badge bg-success">
                                                                <i class="fas fa-check-circle"></i> Terbukti
                                                            </span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                                <?php else: ?>
                                    <div class="alert alert-info">
                                        <i class="fas fa-info-circle"></i> Belum ada indikator yang ditambahkan.
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <h3>Tahap 1</h3>
                        <div class="d-flex justify-content-end mb-2">
                            <?php if ($result && $result->is_leader): ?>
                                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambahStep1">
                                    <i class="fas fa-plus"></i> Tambah Rumusan masalah
                                </button>
                            <?php else: ?>
                                <span class="badge bg-secondary p-2">
                                    <i class="fas fa-lock"></i> Hanya ketua yang dapat menambah
                                </span>
                            <?php endif; ?>
                        </div>
                        <div class="card">
                            <div class="card-header text-white" style="background-color: var(--custom-green);">
                                <h4>Rumusan Masalah</h4>
                            </div>
                            <div class="card-body" style="background-color: var(--custom-blue);">
                                <p><strong>Studi Kasus:</strong> <?php echo nl2br(htmlspecialchars($ebelajar_records->case_study)); ?></p>
                                <p>
                                    <strong>Rumusan masalah:</strong> 
                                    <span class="badge rounded-pill bg-warning text-dark">Belum ada data. Silakan tambahkan rumusan masalah!</span>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="container mx-auto p-3" id="dataStep2">
            <div class="row">
                <div class="col-12">
                    <?php if ($step2_status == "Selesai"): ?>
                        <h3>Tahap 2</h3>
                        <div class="d-flex justify-content-end mb-2">
                            <button class="btn text-white" style="background-color: var(--custom-red);" data-bs-toggle="modal" data-bs-target="#modalEditStep2"><i class="fas fa-plus"></i> Edit File Indikator</button>
                        </div>
                        <div class="card">
                            <div class="card-header text-white" style="background-color: var(--custom-green);">
                                <h4>Penyusunan Indikator</h4>
                            </div>
                            <div class="card-body" style="background-color: var(--custom-blue);">
                                <p><strong>Silahkan anda menambahkan terkait data untuk membuat sebuah pondasi dari kelompok dan upload filenya berupa pdf.</strong></p>
                                <p>
                                    <strong>file:</strong> 
                                    <?php 
                                        if (!empty($step->step2_pondation)) {
                                            echo '<a href="'. (new moodle_url('/mod/ebelajar/' . $step->step2_pondation))->out() .'" download>'.basename($step->step2_pondation).'</a>';
                                        } else {
                                            echo '<span class="badge rounded-pill bg-warning text-dark">Tambahkan file pondasi kelompo anda!</span>';
                                        }
                                    ?>
                                </p>
                            </div>
                        </div>
                    <?php elseif ($step2_status == "Mengerjakan"): ?>
                        <h3>Tahap 2</h3>
                        <div class="d-flex justify-content-end mb-2">
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambahStep2"><i class="fas fa-plus"></i> Tambah File Pondasi Kelompok</button>
                        </div>
                        <div class="card">
                            <div class="card-header text-white" style="background-color: var(--custom-green);">
                                <h4>Penyusunan Indikator</h4>
                            </div>
                            <div class="card-body" style="background-color: var(--custom-blue);">
                                <p><strong>Silahkan anda menambahkan terkait data untuk membuat sebuah pondasi dari kelompok dan upload filenya berupa pdf.</strong></p>
                                <p>
                                    <strong>file:</strong> 
                                    <?php 
                                        if (!empty($step->step2_pondation)) {
                                            echo $step->step2_pondation;
                                        } else {
                                            echo '<span class="badge rounded-pill bg-warning text-dark">Tambahkan file pondasi kelompo anda!</span>';
                                        }
                                    ?>
                                </p>
                            </div>
                        </div>
                    <?php elseif ($step2_status == "Belum Selesai"): ?>
                        <h3>Tahap 2</h3>
                        <div class="alert alert-warning">
                            Kelompok mu belum menyelesaikan tahap 1, selesaikan terlebih dahulu tahap 1.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="container mx-auto p-3" id="dataStep3">
            <div class="row">
                <div class="col-12">
                    <?php if ($step3_schedule_image && $step3_schedule_file != null && $step3_status == "Selesai"): ?>
                        <h3>Tahap 3</h3>
                        <!-- Jika ada data, tampilkan dalam card -->
                        <div class="card">
                            <div class="card-header text-white" style="background-color: var(--custom-green);">
                                <h4>Jadwal Proyek Untuk Siswa</h4>
                            </div>
                            <div class="card-body" style="background-color: var(--custom-blue);">
                                <img src="<?php echo(new moodle_url('/mod/ebelajar/' . $step3_schedule_image))->out(); ?>" alt="gambar jadwal perencanaan" class="d-block mx-auto w-50 h-50">
                                <?php if (!empty($step3_schedule_file)): ?>
                                    <p><strong>File:</strong> <a href="<?php echo $step3_schedule_file; ?>" download><?php echo basename($step3_schedule_file); ?></a></p>
                                <?php else: ?>
                                    <p><strong>File:</strong> Tidak ada file yang diunggah.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php elseif ($step3_schedule_image && $step3_schedule_file == null && $step3_status == "Mengerjakan"): ?>
                        <div class="alert alert-warning">
                            Guru belum menambahkan perjadwalan! harap tunggu sebentar atau silahkan menghubungi guru.
                        </div>
                    <?php else: ?>
                        <h3>Tahap 3</h3>
                        <div class="alert alert-warning">
                            Kelompok mu belum menyelesaikan tahap 2, selesaikan terlebih dahulu tahap 2.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <?php if ($step4_status == "Belum Selesai"): ?>
            <div class="container mx-auto p-3" id="dataStep4">
                <div class="row">
                    <div class="col-12">
                        <h3>Tahap 4</h3>
                        <div class="alert alert-warning">
                            Kelompok mu belum menyelesaikan tahap 3, selesaikan terlebih dahulu tahap 3.
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="container mx-auto px-3 py-5 p-md-5" style="overflow-x: auto;">
                <h3>Tahap 4</h3>
                <div class="d-flex justify-content-between px-0 px-md-3 py-2">
                    <div class="mr-auto"></div>
                    <div class="mr-0 px-0 px-md-3">
                        <button class="btn mr-md-2 mb-2 mb-md-0 rounded-pill px-4" style="background-color: var(--custom-green); color:#FFFFFF" data-bs-toggle="modal" data-bs-target="#modalTambahKegiatan"><i class="fas fa-plus"></i> Tambah Kegiatan</button>
                    </div>
                </div>
                <table id="table" class="table table-striped table-bordered" style="width:100%">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th>Siswa</th>
                            <th>Nama Kegiatan</th>
                            <th>Tanggal Kegiatan</th>
                            <th class="text-center">Foto Kegiatan</th>
                            <th class="text-center">Feedback Guru</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php
                    if ($results2) {
                        $counter = 1;
                        foreach ($results2 as $row) {
                            echo '
                            <tr>
                                <td class="text-center">' . $counter . '</td>
                                <td>' . htmlspecialchars($row->firstname) . ' ' . htmlspecialchars($row->lastname) . '</td>
                                <td>' . htmlspecialchars($row->name_activity) . '</td>
                                <td>' . konversiTanggal($row->date_activity) . '</td>
                                <td class="text-center">
                                    <button class="btn rounded-pill lihat-btn" style="background-color: var(--custom-green); color:#FFFFFF" data-bs-toggle="modal" data-bs-target="#modalLihatFoto" data-foto-url="' . htmlspecialchars($row->file_path) . '">Lihat</button>
                                </td>';

                            if (!empty($row->feedback_teacher)) {
                                echo '<td class="text-left">'. htmlspecialchars($row->feedback_teacher) . '</td>';
                                echo '
                                    <td class="text-center">
                                        <span class="badge rounded-pill text-white" style="background-color: var(--custom-red); color:#FFFFFF">Tidak dapat melakukan perubahan</span>
                                    </td>
                                </tr>';
                            } else {
                                echo '
                                <td class="text-center">
                                    <span class="badge rounded-pill bg-danger text-white">Tidak ada feedback dari guru</span>
                                </td>';
                                echo '
                                    <td class="text-center">
                                        <div class="d-flex justify-content-center gap-2">
                                            <button class="btn rounded-pill btnedit" style="background-color: var(--custom-green); color:#FFFFFF" 
                                                    data-id="' . $row->id . '"
                                                    data-groupproject="' . htmlspecialchars($row->groupproject) . '" 
                                                    data-nama="' . htmlspecialchars($row->name_activity) . '" 
                                                    data-uraian="' . htmlspecialchars($row->description_activity) . '" 
                                                    data-tanggal="' . date('Y-m-d', $row->date_activity) . '"
                                                    data-file="'. $row->file_path .'">
                                                Edit
                                            </button>
                                            <button class="btn btn-danger rounded-pill" onclick="hapusKegiatan(' . $row->id . ')">Hapus</button>
                                        </div>
                                    </td>
                                ';                    
                            }

                            $counter++;
                        }
                    } else {
                        echo "<tr><td colspan='7' class='text-center'>Tidak ada data yang ditemukan.</td></tr>";
                    }
                    ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>


        <div class="container mx-auto p-3" id="dataProjectContainer">
            <div class="row">
                <div class="col-12">
                    <?php if ($project_data && $step5_status == "Selesai"): ?>
                        <h3>Tahap 5</h3>
                        <div class="d-flex justify-content-end mb-2">
                            <button class="btn text-white" style="background-color: var(--custom-red);" data-bs-toggle="modal" data-bs-target="#modalEditProject"><i class="fas fa-plus"></i> Edit Data Project</button>
                        </div>
                        <!-- Jika ada data, tampilkan dalam card -->
                        <div class="card">
                            <div class="card-header text-white" style="background-color: var(--custom-green);">
                                <h4>Data Project Kelompok Anda</h4>
                            </div>
                            <div class="card-body" style="background-color: var(--custom-blue);">
                                <p><strong>Judul Project:</strong> <?php echo $project_data->title_project; ?></p>
                                <p><strong>Deskripsi Project:</strong> <?php echo $project_data->description_project; ?></p>
                                
                                <?php if (!empty($project_data->file_path)): ?>
                                    <p><strong>File:</strong> <a href="<?php echo $project_data->file_path; ?>" download><?php echo basename($project_data->file_path); ?></a></p>
                                <?php else: ?>
                                    <p><strong>File:</strong> Tidak ada file yang diunggah.</p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php elseif ($step5_status == "Mengerjakan"): ?>
                        <h3>Tahap 5</h3>
                        <?php if ($step5_status == "Mengerjakan"): ?>
                            <div class="d-flex justify-content-end mb-2">
                                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambahProject"><i class="fas fa-plus"></i> Tambah Data Project</button>
                            </div>
                        <?php endif; ?>
                        <div class="alert alert-warning">
                            Kelompok mu belum menambahkan data project. Yuk jikalau sudah selesai silahkan dikumpulkan.
                        </div>
                    <?php elseif ($step5_status == "Belum Selesai"): ?>
                        <h3>Tahap 5</h3>
                        <div class="alert alert-warning">
                            Kelompok mu belum menyelesaikan tahap 4, selesaikan terlebih dahulu tahap 4.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="container mx-auto p-3" id="dataStep6">
            <div class="row">
                <div class="col-12">
                    <?php if ($project_data && $step6_status == "Selesai"): ?>
                        <h3>Tahap 6</h3>
                        <div class="d-flex justify-content-end mb-2">
                            <button class="btn text-white" style="background-color: var(--custom-red);" data-bs-toggle="modal" data-bs-target="#modalEditStep6"><i class="fas fa-plus"></i> Edit Evaluasi</button>
                        </div>
                        <!-- Jika ada data, tampilkan dalam card -->
                        <div class="card">
                            <div class="card-header text-white" style="background-color: var(--custom-green);">
                                <h4>Data Evaluasi Kelompokmu</h4>
                            </div>
                            <div class="card-body" style="background-color: var(--custom-blue);">
                                <p><strong>Evaluasi:</strong> <?php echo $project_data->evaluation; ?></p>
                            </div>
                        </div>
                    <?php elseif ($step6_status == "Mengerjakan"): ?>
                        <h3>Tahap 6</h3>
                        <div class="d-flex justify-content-end mb-2">
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambahStep6"><i class="fas fa-plus"></i> Tambah Data Evaluasi</button>
                        </div>
                        <div class="alert alert-warning">
                            Kelompok mu belum menambahkan evaluasi. Yuk jikalau sudah selesai silahkan dikumpulkan.
                        </div>
                    <?php elseif ($step6_status == "Belum Selesai"): ?>
                        <h3>Tahap 6</h3>
                        <div class="alert alert-warning">
                            Kelompok mu belum menyelesaikan tahap 5, selesaikan terlebih dahulu tahap 5.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <div class="modal fade" id="modalTambahProject" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                  <h5 class="modal-title" id="exampleModalLabel">Data Project Kelompok</h5>
              </div>
              <div class="modal-body">
                <form id="formTambahDataProject" method="POST" enctype="multipart/form-data" class="p-4 border rounded bg-light">
                    <input type="hidden" name="group_project" value="<?php echo $result->groupproject; ?>">
                    <input type="hidden" name="cmid" value="<?php echo $cmid; ?>">

                    <!-- Nama Proyek -->
                    <div class="mb-3">
                        <label for="title_project" class="form-label">Nama Proyek</label>
                        <input type="text" id="title_project" name="title_project" 
                            class="form-control rounded-2 px-3 py-2" 
                            style="color:#000000;" 
                            placeholder="Tambah Judul Proyek" required>
                    </div>

                    <!-- Deskripsi Proyek -->
                    <div class="mb-3">
                        <label for="description_project" class="form-label">Deskripsi Proyek</label>
                        <textarea id="description_project" name="description_project" 
                            class="form-control shadow-sm rounded-lg p-3" 
                            placeholder="Tambahkan Deskripsi Proyek" 
                            style="color:#000000; border: 1px solid #000000;" 
                            rows="4" required></textarea>
                    </div>

                    <!-- File Proyek -->
                    <div class="mb-3">
                        <label for="file_project" class="form-label">File Proyek</label>
                        <input type="file" id="file_project" name="file_project" 
                            class="form-control rounded-2 px-3 py-2" 
                            style="color:#000000;">
                    </div>
                </form>
              </div>
              <div class="modal-footer">
                  <button id="btnSimpanProject" type="button" class="btn rounded-pill w-25" style="background-color: var(--custom-green); color:#000000">Simpan</button>
                  <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
    </div>

    <div class="modal fade" id="modalLihatFoto" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background-color: var(--custom-green); color:#000000">
                    <h5 class="modal-title" id="exampleModalLabel">Logo E Learning</h5>
                    <button type="button" class="close" data-bs-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="modalTambahStep1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog modal-xl" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                  <h5 class="modal-title" id="exampleModalLabel">Tambah Jawaban Tahap 1</h5>
              </div>
              <div class="modal-body">
                <form id="formTambahDataStep1" method="POST" class="p-4 border rounded bg-light">
                    <input type="hidden" name="group_project" value="<?php echo $result->groupproject; ?>">
                    <input type="hidden" name="cmid" value="<?php echo $cmid; ?>">
                    <input type="hidden" name="indicators" id="indicatorsData">
                    
                    <!-- Display Scenario from Teacher -->
                    <?php if (!empty($ebelajar_records->teacher_scenario)): ?>
                        <div class="alert alert-info mb-4">
                            <h6 class="fw-bold"><i class="fas fa-book"></i> Skenario dari Guru:</h6>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($ebelajar_records->teacher_scenario)); ?></p>
                        </div>
                    <?php else: ?>
                        <div class="alert alert-secondary mb-4">
                            <h6 class="fw-bold"><i class="fas fa-book"></i> Studi Kasus:</h6>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($ebelajar_records->case_study)); ?></p>
                        </div>
                    <?php endif; ?>

                    <!-- Problem Formulation -->
                    <div class="mb-4">
                        <label for="step1_formulation" class="form-label fw-bold">Rumusan Masalah</label>
                        <textarea id="step1_formulation" name="step1_formulation" 
                            class="form-control shadow-sm rounded-lg p-3" 
                            placeholder="Tulis rumusan masalah hasil diskusi kelompok..." 
                            style="color:#000000; border: 1px solid #000000;" 
                            rows="3" required></textarea>
                    </div>
                    
                    <!-- Dynamic Indicators Table -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Analisis Indikator & Referensi</label>
                        <div class="table-responsive">
                            <table class="table table-bordered table-hover">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 25%">Nama Indikator</th>
                                        <th style="width: 30%">Analisis</th>
                                        <th style="width: 30%">Referensi (Min. 3)</th>
                                        <th style="width: 10%">Status</th>
                                        <th style="width: 5%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody id="indicatorRows">
                                    <!-- Rows will be added dynamically -->
                                </tbody>
                            </table>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddRow">
                            <i class="fas fa-plus"></i> Tambah Indikator
                        </button>
                        <small class="text-muted d-block mt-2">
                            <i class="fas fa-info-circle"></i> Setiap indikator harus memiliki minimal 3 referensi (URL/sumber).
                        </small>
                    </div>
                    
                    <?php if (!$result->is_leader): ?>
                        <div class="alert alert-warning">
                            <i class="fas fa-lock"></i> Hanya ketua kelompok yang dapat menyimpan data.
                        </div>
                    <?php endif; ?>
                </form>
              </div>
              <div class="modal-footer">
                  <button id="btnSimpanStep1" type="button" class="btn rounded-pill px-4" 
                          style="background-color: var(--custom-green); color:#ffffff"
                          <?php echo (!$result->is_leader) ? 'disabled title="Hanya ketua kelompok yang dapat menyimpan"' : ''; ?>>
                      <i class="fas fa-save"></i> Simpan
                  </button>
                  <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
    </div>

    <div class="modal fade" id="modalTambahStep2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                  <h5 class="modal-title" id="exampleModalLabel">Tambah Jawaban Tahap 2</h5>
              </div>
              <div class="modal-body">
                <form id="formTambahDataStep2" method="POST" enctype="multipart/form-data" class="p-4 border rounded bg-light">
                    <input type="hidden" name="group_project" value="<?php echo $result->groupproject; ?>">
                    <input type="hidden" name="cmid" value="<?php echo $cmid; ?>">

                    <!-- File Step 2 -->
                    <div class="mb-3">
                        <label for="step2_pondation" class="form-label">Upload FIle</label>
                        <input type="file" id="step2_pondation" name="step2_pondation" 
                            class="form-control rounded-2 px-3 py-2" 
                            style="color:#000000;" accept=".pdf" required>
                    </div>
                </form>
              </div>
              <div class="modal-footer">
                  <button id="btnSimpanStep2" type="button" class="btn rounded-pill w-25" style="background-color: var(--custom-green); color:#ffffff">Simpan</button>
                  <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
    </div>

    <div class="modal fade" id="modalEditProject" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                  <h5 class="modal-title" id="exampleModalLabel">Data Project Kelompok</h5>
              </div>
              <div class="modal-body">
                <form id="formEditDataProject" method="POST" enctype="multipart/form-data" class="p-4 border rounded bg-light">
                    <input type="hidden" name="id" value="<?php echo $project_data->id; ?>">
                    <input type="hidden" name="group_project" value="<?php echo $result->groupproject; ?>">
                    <input type="hidden" name="cmid" value="<?php echo $cmid; ?>">

                    <!-- Nama Proyek -->
                    <div class="mb-3">
                        <label for="title_project" class="form-label">Nama Proyek</label>
                        <input type="text" id="title_project" name="title_project" 
                            class="form-control rounded-2 px-3 py-2" 
                            style="color:#000000;" 
                            placeholder="Tambah Judul Proyek" 
                            value="<?php echo $project_data->title_project; ?>" required>
                    </div>

                    <!-- Deskripsi Proyek -->
                    <div class="mb-3">
                        <label for="description_project" class="form-label">Deskripsi Proyek</label>
                        <textarea id="description_project" name="description_project" 
                            class="form-control shadow-sm rounded-lg p-3" 
                            placeholder="Tambahkan Deskripsi Proyek" 
                            style="color:#000000; border: 1px solid #000000;" 
                            rows="4" required><?php echo htmlspecialchars($project_data->description_project); ?>
                        </textarea>

                    </div>

                    <!-- File Proyek -->
                    <div class="mb-3">
                        <label for="file_project" class="form-label">File Proyek</label>
                        <input type="file" id="file_project" name="file_project" 
                            class="form-control rounded-2 px-3 py-2" 
                            style="color:#000000;">
                    </div>
                </form>
              </div>
              <div class="modal-footer">
                  <button id="btnUpdateProject" type="button" class="btn rounded-pill w-25" style="background-color: var(--custom-green); color:#ffffff">Update</button>
                  <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
    </div>

    <div class="modal fade" id="modalTambahKegiatan" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                    <h5 class="modal-title" id="exampleModalLabel">Kegiatan</h5>
                </div>
                <div class="modal-body">
                    <form id="formTambahDataActivity" method="POST" enctype="multipart/form-data" class="p-4 border rounded bg-light">
                        <input type="hidden" name="cmid" value="<?php echo $cmid; ?>">
                        <input type="hidden" name="group_project" value="<?php echo $result->groupproject; ?>">

                        <!-- Nama Kegiatan -->
                        <div class="mb-3">
                            <label for="nama_kegiatan" class="form-label">Nama Kegiatan</label>
                            <input type="text" id="nama_kegiatan" name="nama_kegiatan" 
                                class="form-control rounded-2 px-3 py-2" 
                                style="color:#000000;" 
                                placeholder="Tambah Kegiatan" required>
                        </div>

                        <!-- Uraian Kegiatan -->
                        <div class="mb-3">
                            <label for="uraian_kegiatan" class="form-label">Uraian Kegiatan</label>
                            <textarea id="uraian_kegiatan" name="uraian_kegiatan" 
                                class="form-control shadow-sm rounded-lg p-3" 
                                style="color:#000000; border: 1px solid #000000;" 
                                placeholder="Masukkan Uraian Kegiatan" rows="4" required></textarea>
                        </div>

                        <!-- Tanggal Kegiatan -->
                        <div class="mb-3">
                            <label for="tanggal_kegiatan" class="form-label">Tanggal Kegiatan</label>
                            <input type="date" id="tanggal_kegiatan" name="tanggal_kegiatan" 
                                class="form-control rounded-2 px-3 py-2" 
                                style="color:#000000;" required>
                        </div>

                        <!-- Bukti Kegiatan -->
                        <div class="mb-3">
                            <label for="bukti_kegiatan" class="form-label">Bukti Kegiatan</label>
                            <input type="file" id="bukti_kegiatan" name="bukti_kegiatan" 
                                class="form-control rounded-2 px-3 py-2" 
                                style="color:#000000;" accept=".jpg, .jpeg, .png, .gif" required>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button id="btnSimpanActivity" type="button" class="btn rounded-pill w-25" style="background-color: var(--custom-green); color:#ffffff">Simpan</button>
                    <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalTambahStep6" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                  <h5 class="modal-title" id="exampleModalLabel">Tambah Jawaban Tahap 6</h5>
              </div>
              <div class="modal-body">
                <form id="formTambahDataStep6" method="POST" enctype="multipart/form-data" class="p-4 border rounded bg-light">
                    <input type="hidden" name="group_project" value="<?php echo $result->groupproject; ?>">
                    <input type="hidden" name="cmid" value="<?php echo $cmid; ?>">

                    <!-- Evaluasi -->
                    <div class="mb-3">
                        <label for="evaluation" class="form-label">Evaluasi / Saran</label>
                        <textarea id="evaluation" name="evaluation" 
                            class="form-control shadow-sm rounded-lg p-3" 
                            placeholder="Tambahkan evaluasi" 
                            style="color:#000000; border: 1px solid #000000;" 
                            rows="4" required></textarea>
                    </div>
                </form>
              </div>
              <div class="modal-footer">
                  <button id="btnSimpanStep6" type="button" class="btn rounded-pill w-25" style="background-color: var(--custom-green); color:#ffffff">Simpan</button>
                  <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
    </div>


    <div class="modal fade" id="modalEditStep1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog modal-xl" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                  <h5 class="modal-title" id="exampleModalLabel">Edit Rumusan Masalah</h5>
              </div>
              <div class="modal-body">
                <div id="editLoad">
                    <form id="formUbahStep1" method="POST" class="p-4 border rounded bg-light">
                        <input type="hidden" name="group_project" value="<?php echo $result->groupproject; ?>">
                        <input type="hidden" name="cmid" value="<?php echo $cmid; ?>">
                        <input type="hidden" name="indicators" id="indicatorsDataEdit">
                        
                        <!-- Display Scenario from Teacher -->
                        <?php if (!empty($ebelajar_records->teacher_scenario)): ?>
                            <div class="alert alert-info mb-4">
                                <h6 class="fw-bold"><i class="fas fa-book"></i> Skenario dari Guru:</h6>
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($ebelajar_records->teacher_scenario)); ?></p>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-secondary mb-4">
                                <h6 class="fw-bold"><i class="fas fa-book"></i> Studi Kasus:</h6>
                                <p class="mb-0"><?php echo nl2br(htmlspecialchars($ebelajar_records->case_study)); ?></p>
                            </div>
                        <?php endif; ?>

                        <!-- Problem Formulation -->
                        <div class="mb-4">
                            <label for="step1_formulation_edit" class="form-label fw-bold">Rumusan Masalah</label>
                            <textarea id="step1_formulation_edit" name="step1_formulation" 
                                class="form-control shadow-sm rounded-lg p-3" 
                                placeholder="Tulis rumusan masalah hasil diskusi kelompok..." 
                                style="color:#000000; border: 1px solid #000000;" 
                                rows="3" required><?php echo htmlspecialchars($project_data->step1_formulation ?? ''); ?></textarea>
                        </div>
                        
                        <!-- Dynamic Indicators Table -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Analisis Indikator & Referensi</label>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="table-light">
                                        <tr>
                                            <th style="width: 25%">Nama Indikator</th>
                                            <th style="width: 30%">Analisis</th>
                                            <th style="width: 30%">Referensi (Min. 3)</th>
                                            <th style="width: 10%">Status</th>
                                            <th style="width: 5%">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody id="indicatorRowsEdit">
                                        <!-- Rows will be loaded from existing data -->
                                    </tbody>
                                </table>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddRowEdit">
                                <i class="fas fa-plus"></i> Tambah Indikator
                            </button>
                            <small class="text-muted d-block mt-2">
                                <i class="fas fa-info-circle"></i> Setiap indikator harus memiliki minimal 3 referensi (URL/sumber).
                            </small>
                        </div>
                        
                        <?php if (!$result->is_leader): ?>
                            <div class="alert alert-warning">
                                <i class="fas fa-lock"></i> Hanya ketua kelompok yang dapat menyimpan data.
                            </div>
                        <?php endif; ?>
                    </form>
                </div>
              </div>
              <div class="modal-footer">
                  <button id="btnUpdatedStep1" type="button" class="btn rounded-pill px-4" 
                          style="background-color: var(--custom-green); color:#ffffff"
                          <?php echo (!$result->is_leader) ? 'disabled title="Hanya ketua kelompok yang dapat menyimpan"' : ''; ?>>
                      <i class="fas fa-save"></i> Simpan
                  </button>
                  <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
    </div>

    <div class="modal fade" id="modalEditStep2" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                  <h5 class="modal-title" id="exampleModalLabel">Edit Penyusunan Indikator</h5>
              </div>
              <div class="modal-body">

                <div id="editLoad">
                    <form id="formUbahStep2" method="POST" enctype="multipart/form-data" class="p-3 border rounded bg-light">
                        <input type="hidden" name="group_project" value="<?php echo $result->groupproject; ?>">
                        <input type="hidden" name="cmid" value="<?php echo $cmid; ?>">

                        <!-- File Step2 -->
                        <div class="mb-3">
                            <label for="step2_pondation" class="form-label">Upload File</label>
                            <input type="file" id="step2_pondation" name="step2_pondation" 
                                class="form-control rounded-2 px-3 py-2" 
                                style="color:#000000;" accept=".pdf">
                        </div>

                    </form>
                </div>
                
              </div>
              <div class="modal-footer">
                  <button id="btnUpdatedStep2" type="button" class="btn rounded-pill w-25" style="background-color: var(--custom-green); color:#ffffff">Simpan</button>
                  <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
    </div>

    <div class="modal fade" id="modalEditStep6" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                  <h5 class="modal-title" id="exampleModalLabel">Edit Evaluasi</h5>
              </div>
              <div class="modal-body">

                <div id="editLoad">
                    <form id="formUbahStep6" method="POST" class="p-3 border rounded bg-light">
                        <input type="hidden" name="group_project" value="<?php echo $result->groupproject; ?>">
                        <input type="hidden" name="cmid" value="<?php echo $cmid; ?>">

                        <div class="mb-3">
                            <label for="evaluation" class="form-label">Evaluasi / Saran</label>
                            <textarea id="evaluation" name="evaluation" 
                                class="form-control shadow-sm rounded-lg p-3" 
                                placeholder="Tambahkan evaluasi" 
                                style="color:#000000; border: 1px solid #000000;" 
                                rows="4" required><?php echo $project_data->evaluation; ?></textarea>
                        </div>
                    </form>
                </div>
                
              </div>
              <div class="modal-footer">
                  <button id="btnUpdatedStep6" type="button" class="btn rounded-pill w-25" style="background-color: var(--custom-green); color:#ffffff">Simpan</button>
                  <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
    </div>

    <div class="modal fade" id="modalLihatKelompok" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                  <h5 class="modal-title" id="exampleModalLabel">Pilih Kelompok Untuk Dilihat</h5>
              </div>
              <div class="modal-body">

                <div id="editLoad">
                    <form id="formLihatKelompok" method="POST" class="p-3 border rounded bg-light">
                        <input type="hidden" name="cmid" value="<?php echo $cmid; ?>">

                        <div class="mb-3 d-flex flex-column">
                            <label for="lihat_kelompok" class="form-label">Pilih kelompok yang ingin dilihat</label>
                            
                            <select id="lihat_kelompok" name="lihat_kelompok" 
                                    class="form-select rounded-2 px-3 py-2 mt-2" 
                                    style="color:#000000;" required>
                                <option value="">Pilih No Kelompok</option>
                                <?php
                                foreach ($filtered_records as $record) {
                                    echo '<option value="' . $record->group_project . '">Kelompok ' . $record->group_number . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </form>
                </div>
                
              </div>
              <div class="modal-footer">
                  <button id="btnSubmitLihat" type="button" class="btn rounded-pill w-25" style="background-color: var(--custom-green); color:#ffffff">lihat</button>
                  <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
    </div>