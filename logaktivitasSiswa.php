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
                            <button class="btn text-white" style="background-color: var(--custom-red);" data-bs-toggle="modal" data-bs-target="#modalEditStep1"><i class="fas fa-plus"></i> Edit Rumusan masalah</button>
                        </div>
                        <div class="card">
                            <div class="card-header text-white" style="background-color: var(--custom-green);">
                                <h4>Rumusan Masalah & Analisis</h4>
                            </div>
                            <div class="card-body" style="background-color: var(--custom-blue);">
                                <p><strong>Studi Kasus:</strong> <?php echo $ebelajar_records->case_study; ?></p>
                                <p>
                                    <strong>Rumusan masalah:</strong> 
                                    <?php 
                                        if (!empty($step->step1_formulation)) {
                                            echo $step->step1_formulation;
                                        } else {
                                            echo '<span class="badge rounded-pill bg-warning text-dark">Tambahkan rumusan masalah menurut kelompok anda!</span>';
                                        }
                                    ?>
                                </p>
                                
                                <?php if (!empty($step->problem_definition)): ?>
                                <p>
                                    <strong>Orientasi Masalah:</strong> 
                                    <?php echo $step->problem_definition; ?>
                                </p>
                                <?php endif; ?>
                                
                                <?php 
                                // Display saved indicators (Selesai view)
                                if (!empty($step->analysis_data)) {
                                    $indicators_selesai = json_decode($step->analysis_data, true);
                                    if (is_array($indicators_selesai) && count($indicators_selesai) > 0):
                                ?>
                                <div class="mt-3">
                                    <strong>Indikator Penyebab Masalah:</strong>
                                    <div class="mt-2">
                                        <?php foreach ($indicators_selesai as $idx => $ind): ?>
                                        <div class="card mb-2" style="border-left: 4px solid var(--custom-green);">
                                            <div class="card-body py-2 px-3">
                                                <div class="d-flex align-items-start">
                                                    <span class="badge bg-success me-2"><?php echo ($idx + 1); ?></span>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($ind['indicator'] ?? ''); ?></strong>
                                                        <?php if (!empty($ind['analysis'])): ?>
                                                        <p class="mb-0 mt-1 small text-muted">
                                                            <?php echo nl2br(htmlspecialchars($ind['analysis'])); ?>
                                                        </p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php 
                                    endif;
                                }
                                ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <h3>Tahap 1</h3>
                        <div class="d-flex justify-content-end mb-2">
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambahStep1"><i class="fas fa-plus"></i> Tambah Rumusan masalah</button>
                        </div>
                        <div class="card">
                            <div class="card-header text-white" style="background-color: var(--custom-green);">
                                <h4>Rumusan Masalah</h4>
                            </div>
                            <div class="card-body" style="background-color: var(--custom-blue);">
                                <p><strong>Studi Kasus:</strong> <?php echo $ebelajar_records->case_study; ?></p>
                                <p>
                                    <strong>Rumusan masalah:</strong> 
                                    <?php 
                                        if (!empty($step->step1_formulation)) {
                                            echo $step->step1_formulation;
                                        } else {
                                            echo '<span class="badge rounded-pill bg-warning text-dark">Tambahkan rumusan masalah menurut kelompok anda!</span>';
                                        }
                                    ?>
                                </p>
                                
                                <?php if (!empty($step->problem_definition)): ?>
                                <p>
                                    <strong>Orientasi Masalah:</strong> 
                                    <?php echo $step->problem_definition; ?>
                                </p>
                                <?php endif; ?>
                                
                                <?php 
                                // Display saved indicators
                                if (!empty($step->analysis_data)) {
                                    $indicators = json_decode($step->analysis_data, true);
                                    if (is_array($indicators) && count($indicators) > 0):
                                ?>
                                <div class="mt-3">
                                    <strong>Indikator Penyebab Masalah:</strong>
                                    <div class="mt-2">
                                        <?php foreach ($indicators as $index => $item): ?>
                                        <div class="card mb-2" style="border-left: 4px solid var(--custom-green);">
                                            <div class="card-body py-2 px-3">
                                                <div class="d-flex align-items-start">
                                                    <span class="badge bg-success me-2"><?php echo ($index + 1); ?></span>
                                                    <div>
                                                        <strong><?php echo htmlspecialchars($item['indicator'] ?? ''); ?></strong>
                                                        <?php if (!empty($item['analysis'])): ?>
                                                        <p class="mb-0 mt-1 small text-muted">
                                                            <?php echo nl2br(htmlspecialchars($item['analysis'])); ?>
                                                        </p>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                                <?php 
                                    endif;
                                }
                                ?>
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
                            <button class="btn text-white" style="background-color: var(--custom-red);" data-bs-toggle="modal" data-bs-target="#modalEditStep2"><i class="fas fa-edit"></i> Edit Jadwal Proyek</button>
                        </div>
                        <div class="card">
                            <div class="card-header text-white" style="background-color: var(--custom-green);">
                                <h4>Penyusunan Jadwal Proyek</h4>
                            </div>
                            <div class="card-body" style="background-color: var(--custom-blue);">
                                <p><strong>Jadwal pelaksanaan proyek kelompok Anda:</strong></p>
                                <?php 
                                if (!empty($step->planning_data)) {
                                    $schedule_data = json_decode($step->planning_data, true);
                                    if (is_array($schedule_data) && count($schedule_data) > 0):
                                ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped bg-white">
                                        <thead class="table-success">
                                            <tr>
                                                <th style="width: 50px;">No</th>
                                                <th>Kegiatan</th>
                                                <th style="width: 130px;">Tanggal Mulai</th>
                                                <th style="width: 130px;">Tanggal Selesai</th>
                                                <th>Penanggung Jawab</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($schedule_data as $idx => $task): ?>
                                            <tr>
                                                <td class="text-center"><?php echo ($idx + 1); ?></td>
                                                <td><?php echo htmlspecialchars($task['task'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($task['start_date'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($task['end_date'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($task['pic'] ?? ''); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php 
                                    else:
                                        echo '<div class="alert alert-info">Belum ada jadwal yang disusun.</div>';
                                    endif;
                                } else {
                                    echo '<div class="alert alert-info">Belum ada jadwal yang disusun.</div>';
                                }
                                ?>
                            </div>
                        </div>
                    <?php elseif ($step2_status == "Mengerjakan"): ?>
                        <h3>Tahap 2</h3>
                        <div class="d-flex justify-content-end mb-2">
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambahStep2"><i class="fas fa-plus"></i> Tambah Jadwal Proyek</button>
                        </div>
                        <div class="card">
                            <div class="card-header text-white" style="background-color: var(--custom-green);">
                                <h4>Penyusunan Jadwal Proyek</h4>
                            </div>
                            <div class="card-body" style="background-color: var(--custom-blue);">
                                <p><strong>Silahkan susun jadwal pelaksanaan proyek kelompok Anda.</strong></p>
                                <?php 
                                if (!empty($step->planning_data)) {
                                    $schedule_data = json_decode($step->planning_data, true);
                                    if (is_array($schedule_data) && count($schedule_data) > 0):
                                ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped bg-white">
                                        <thead class="table-success">
                                            <tr>
                                                <th style="width: 50px;">No</th>
                                                <th>Kegiatan</th>
                                                <th style="width: 130px;">Tanggal Mulai</th>
                                                <th style="width: 130px;">Tanggal Selesai</th>
                                                <th>Penanggung Jawab</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach ($schedule_data as $idx => $task): ?>
                                            <tr>
                                                <td class="text-center"><?php echo ($idx + 1); ?></td>
                                                <td><?php echo htmlspecialchars($task['task'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($task['start_date'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($task['end_date'] ?? ''); ?></td>
                                                <td><?php echo htmlspecialchars($task['pic'] ?? ''); ?></td>
                                            </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <?php 
                                    else:
                                        echo '<div class="alert alert-warning">Belum ada jadwal yang disusun. Klik tombol "Tambah Jadwal Proyek" untuk memulai.</div>';
                                    endif;
                                } else {
                                    echo '<div class="alert alert-warning">Belum ada jadwal yang disusun. Klik tombol "Tambah Jadwal Proyek" untuk memulai.</div>';
                                }
                                ?>
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



    <!-- MODAL TAMBAH STEP 2 - JADWAL PROYEK (INLINE JS) -->
    <div class="modal fade" id="modalTambahStep2" tabindex="-1" role="dialog" aria-labelledby="modalTambahStep2Label" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                  <h5 class="modal-title" id="modalTambahStep2Label">Tambah Jadwal Proyek</h5>
              </div>
              <div class="modal-body">
                <form id="formTambahDataStep2" method="POST" class="p-3 border rounded bg-light">
                    <input type="hidden" name="group_project" value="<?php echo $result->groupproject; ?>">
                    <input type="hidden" name="cmid" value="<?php echo $cmid; ?>">

                    <!-- Hidden field for final JSON -->
                    <textarea name="planning_data" id="planning_data_add" style="display:none;"></textarea>

                    <!-- Schedule Rows Container -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Jadwal Kegiatan Proyek</label>
                        <p class="text-muted small mb-2">Tambahkan kegiatan proyek beserta jadwal dan penanggung jawab.</p>
                        
                        <!-- Container for schedule rows -->
                        <div id="schedule-container-add" class="mb-3">
                            <!-- Initial row will be added by JavaScript -->
                        </div>
                        
                        <!-- Add button -->
                        <button type="button" id="btn-add-schedule-add" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-plus"></i> Tambah Kegiatan
                        </button>
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

    <script>
    (function() {
        'use strict';
        
        function getScheduleRowTemplateAdd(index) {
            return '<div class="schedule-row card mb-2 p-3 bg-white border" style="border-left: 4px solid #17a2b8 !important;">' +
                '<div class="d-flex justify-content-between align-items-start mb-2">' +
                    '<span class="badge bg-info row-number">Kegiatan #' + index + '</span>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger btn-remove-schedule">' +
                        '<i class="fas fa-trash"></i> Hapus' +
                    '</button>' +
                '</div>' +
                '<div class="mb-2">' +
                    '<label class="form-label small">Nama Kegiatan</label>' +
                    '<input type="text" class="form-control schedule-task" placeholder="Contoh: Riset Awal" required>' +
                '</div>' +
                '<div class="row mb-2">' +
                    '<div class="col-md-6">' +
                        '<label class="form-label small">Tanggal Mulai</label>' +
                        '<input type="date" class="form-control schedule-start">' +
                    '</div>' +
                    '<div class="col-md-6">' +
                        '<label class="form-label small">Tanggal Selesai</label>' +
                        '<input type="date" class="form-control schedule-end">' +
                    '</div>' +
                '</div>' +
                '<div class="mb-0">' +
                    '<label class="form-label small">Penanggung Jawab (PIC)</label>' +
                    '<input type="text" class="form-control schedule-pic" placeholder="Nama anggota yang bertanggung jawab">' +
                '</div>' +
            '</div>';
        }

        function updateScheduleRowNumbersAdd() {
            var container = document.getElementById('schedule-container-add');
            var rows = container.querySelectorAll('.schedule-row');
            rows.forEach(function(row, index) {
                var badge = row.querySelector('.row-number');
                badge.textContent = 'Kegiatan #' + (index + 1);
                
                var deleteBtn = row.querySelector('.btn-remove-schedule');
                if (rows.length > 1) {
                    deleteBtn.style.display = '';
                } else {
                    deleteBtn.style.display = 'none';
                }
            });
        }

        window.addScheduleRowAdd = function() {
            var container = document.getElementById('schedule-container-add');
            var currentRows = container.querySelectorAll('.schedule-row').length;
            var newIndex = currentRows + 1;
            
            container.insertAdjacentHTML('beforeend', getScheduleRowTemplateAdd(newIndex));
            updateScheduleRowNumbersAdd();
            
            var newRow = container.lastElementChild;
            var deleteBtn = newRow.querySelector('.btn-remove-schedule');
            deleteBtn.addEventListener('click', function() {
                removeScheduleRowAdd(this);
            });
        };

        function removeScheduleRowAdd(btn) {
            var container = document.getElementById('schedule-container-add');
            var rows = container.querySelectorAll('.schedule-row');
            
            if (rows.length > 1) {
                var row = btn.closest('.schedule-row');
                row.remove();
                updateScheduleRowNumbersAdd();
            }
        }

        function serializeScheduleAdd() {
            var schedule = [];
            var container = document.getElementById('schedule-container-add');
            var rows = container.querySelectorAll('.schedule-row');
            
            rows.forEach(function(row) {
                var task = row.querySelector('.schedule-task').value;
                var startDate = row.querySelector('.schedule-start').value;
                var endDate = row.querySelector('.schedule-end').value;
                var pic = row.querySelector('.schedule-pic').value;
                
                if (task && task.trim()) {
                    schedule.push({
                        task: task.trim(),
                        start_date: startDate || '',
                        end_date: endDate || '',
                        pic: pic ? pic.trim() : ''
                    });
                }
            });
            
            return JSON.stringify(schedule);
        }

        var modalElementAdd = document.getElementById('modalTambahStep2');
        modalElementAdd.addEventListener('shown.bs.modal', function() {
            var container = document.getElementById('schedule-container-add');
            container.innerHTML = '';
            addScheduleRowAdd();
        });

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('btn-add-schedule-add').addEventListener('click', addScheduleRowAdd);
            
            document.getElementById('btnSimpanStep2').addEventListener('click', function() {
                var container = document.getElementById('schedule-container-add');
                var rows = container.querySelectorAll('.schedule-row');
                var hasValidRow = false;
                
                rows.forEach(function(row) {
                    var task = row.querySelector('.schedule-task').value;
                    if (task && task.trim()) {
                        hasValidRow = true;
                    }
                });
                
                if (!hasValidRow) {
                    alert('Harap tambahkan minimal satu kegiatan dengan nama kegiatan.');
                    return;
                }
                
                var jsonPayload = serializeScheduleAdd();
                document.getElementById('planning_data_add').value = jsonPayload;
                
                var formData = new FormData(document.getElementById('formTambahDataStep2'));
                
                fetch('formtambahDataStep2.php', {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) {
                    return response.text();
                })
                .then(function(data) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Jadwal berhasil disimpan!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        document.getElementById('formTambahDataStep2').reset();
                        var modal = bootstrap.Modal.getInstance(document.getElementById('modalTambahStep2'));
                        modal.hide();
                        location.reload();
                    });
                })
                .catch(function(error) {
                    alert('Terjadi kesalahan saat menyimpan jadwal.');
                });
            });
        });
    })();
    </script>

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


    <!-- MODAL TAMBAH STEP 1 - NUCLEAR FIX (INLINE JS) -->
    <div class="modal fade" id="modalTambahStep1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                  <h5 class="modal-title" id="exampleModalLabel">Tambah Rumusan Masalah</h5>
              </div>
              <div class="modal-body">
                <form id="formTambahDataStep1" method="POST" class="p-3 border rounded bg-light">
                    <input type="hidden" name="group_project" value="<?php echo $result->groupproject; ?>">
                    <input type="hidden" name="cmid" value="<?php echo $cmid; ?>">

                    <!-- Rumusan Masalah -->
                    <div class="mb-3">
                        <label for="step1_formulation_add" class="form-label">Rumusan Masalah</label>
                        <textarea id="step1_formulation_add" name="step1_formulation" 
                            class="form-control shadow-sm rounded-lg p-3" 
                            placeholder="Tambahkan Rumusan Masalah" 
                            style="color:#000000; border: 1px solid #000000;" 
                            rows="4" required></textarea>
                    </div>

                    <!-- Orientasi Masalah (Problem Definition) -->
                    <div class="mb-3">
                        <label for="problem_definition_add" class="form-label">Orientasi Masalah</label>
                        <textarea id="problem_definition_add" name="problem_definition" 
                            class="form-control shadow-sm rounded-lg p-3" 
                            placeholder="Jelaskan orientasi dan latar belakang masalah" 
                            style="color:#000000; border: 1px solid #000000;" 
                            rows="4"></textarea>
                    </div>

                    <!-- Analisis & Indikator (Visual Repeater - Add) -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Indikator Penyebab Masalah</label>
                        <p class="text-muted small mb-2">Tambahkan minimal 1 indikator penyebab masalah beserta analisis Anda.</p>
                        
                        <!-- Hidden field for final JSON -->
                        <textarea name="analysis_data" id="analysis_data_add" style="display:none;"></textarea>
                        
                        <!-- Container for indicator rows -->
                        <div id="indicator-container-add" class="mb-3">
                            <!-- Initial row will be added by JavaScript -->
                        </div>
                        
                        <!-- Add button -->
                        <button type="button" id="btn-add-indicator-add" class="btn btn-outline-success btn-sm">
                            <i class="fas fa-plus"></i> Tambah Indikator Masalah
                        </button>
                    </div>

                </form>
              </div>
              <div class="modal-footer">
                  <button id="btnSimpanStep1" type="button" class="btn rounded-pill w-25" style="background-color: var(--custom-green); color:#ffffff">Simpan</button>
                  <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
    </div>

    <script>
    (function() {
        'use strict';
        
        function getRowTemplate(index) {
            return '<div class="indicator-row card mb-2 p-3 bg-white border" style="border-left: 4px solid #28a745 !important;">' +
                '<div class="d-flex justify-content-between align-items-start mb-2">' +
                    '<span class="badge bg-success row-number">Indikator #' + index + '</span>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger btn-remove-indicator">' +
                        '<i class="fas fa-trash"></i> Hapus' +
                    '</button>' +
                '</div>' +
                '<div class="mb-2">' +
                    '<label class="form-label small">Indikator Penyebab</label>' +
                    '<input type="text" class="form-control indicator-cause" placeholder="Contoh: Kurangnya pemahaman siswa" required>' +
                '</div>' +
                '<div class="mb-0">' +
                    '<label class="form-label small">Analisis & Sumber Referensi</label>' +
                    '<textarea class="form-control indicator-analysis" rows="2" placeholder="Jelaskan analisis dan sumber referensi"></textarea>' +
                '</div>' +
            '</div>';
        }

        function updateRowNumbers() {
            var container = document.getElementById('indicator-container-add');
            var rows = container.querySelectorAll('.indicator-row');
            rows.forEach(function(row, index) {
                var badge = row.querySelector('.row-number');
                badge.textContent = 'Indikator #' + (index + 1);
                
                var deleteBtn = row.querySelector('.btn-remove-indicator');
                if (rows.length > 1) {
                    deleteBtn.style.display = '';
                } else {
                    deleteBtn.style.display = 'none';
                }
            });
        }

        window.addIndicatorRow = function() {
            var container = document.getElementById('indicator-container-add');
            var currentRows = container.querySelectorAll('.indicator-row').length;
            var newIndex = currentRows + 1;
            
            container.insertAdjacentHTML('beforeend', getRowTemplate(newIndex));
            updateRowNumbers();
            
            var newRow = container.lastElementChild;
            var deleteBtn = newRow.querySelector('.btn-remove-indicator');
            deleteBtn.addEventListener('click', function() {
                removeIndicatorRow(this);
            });
        };

        function removeIndicatorRow(btn) {
            var container = document.getElementById('indicator-container-add');
            var rows = container.querySelectorAll('.indicator-row');
            
            if (rows.length > 1) {
                var row = btn.closest('.indicator-row');
                row.remove();
                updateRowNumbers();
            }
        }

        function serializeIndicators() {
            var indicators = [];
            var container = document.getElementById('indicator-container-add');
            var rows = container.querySelectorAll('.indicator-row');
            
            rows.forEach(function(row) {
                var cause = row.querySelector('.indicator-cause').value;
                var analysis = row.querySelector('.indicator-analysis').value;
                if (cause && cause.trim()) {
                    indicators.push({
                        indicator: cause.trim(),
                        analysis: analysis ? analysis.trim() : ''
                    });
                }
            });
            
            return JSON.stringify(indicators);
        }

        var modalElement = document.getElementById('modalTambahStep1');
        modalElement.addEventListener('shown.bs.modal', function() {
            var container = document.getElementById('indicator-container-add');
            container.innerHTML = '';
            addIndicatorRow();
        });

        document.addEventListener('DOMContentLoaded', function() {
            document.getElementById('btn-add-indicator-add').addEventListener('click', addIndicatorRow);
            
            document.getElementById('btnSimpanStep1').addEventListener('click', function() {
                var formulation = document.getElementById('step1_formulation_add').value;
                if (!formulation || !formulation.trim()) {
                    alert('Harap lengkapi rumusan masalah.');
                    return;
                }
                
                var jsonPayload = serializeIndicators();
                document.getElementById('analysis_data_add').value = jsonPayload;
                
                var formData = new FormData(document.getElementById('formTambahDataStep1'));
                
                fetch('formtambahDataStep1.php', {
                    method: 'POST',
                    body: formData
                })
                .then(function(response) {
                    return response.text();
                })
                .then(function(data) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Data berhasil disimpan!',
                        showConfirmButton: false,
                        timer: 1500
                    }).then(function() {
                        document.getElementById('formTambahDataStep1').reset();
                        var modal = bootstrap.Modal.getInstance(document.getElementById('modalTambahStep1'));
                        modal.hide();
                        location.reload();
                    });
                })
                .catch(function(error) {
                    alert('Terjadi kesalahan saat menyimpan data.');
                });
            });
        });
    })();
    </script>

    <div class="modal fade" id="modalEditStep1" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                  <h5 class="modal-title" id="exampleModalLabel">Edit Rumusan Masalah</h5>
              </div>
              <div class="modal-body">

                <div id="editLoad">
                    <form id="formUbahStep1" method="POST" class="p-3 border rounded bg-light">
                        <input type="hidden" name="group_project" value="<?php echo $result->groupproject; ?>">
                        <input type="hidden" name="cmid" value="<?php echo $cmid; ?>">

                        <!-- Rumusan Masalah -->
                        <div class="mb-3">
                            <label for="step1_formulation" class="form-label">Rumusan Masalah</label>
                            <textarea id="step1_formulation" name="step1_formulation" 
                                class="form-control shadow-sm rounded-lg p-3" 
                                placeholder="Tambahkan Deskripsi Proyek" 
                                style="color:#000000; border: 1px solid #000000;" 
                                rows="4" required><?php echo isset($step->step1_formulation) ? $step->step1_formulation : ''; ?></textarea>
                        </div>

                        <!-- Orientasi Masalah (Problem Definition) -->
                        <div class="mb-3">
                            <label for="problem_definition" class="form-label">Orientasi Masalah</label>
                            <textarea id="problem_definition" name="problem_definition" 
                                class="form-control shadow-sm rounded-lg p-3" 
                                placeholder="Jelaskan orientasi dan latar belakang masalah" 
                                style="color:#000000; border: 1px solid #000000;" 
                                rows="4"><?php echo isset($step->problem_definition) ? $step->problem_definition : ''; ?></textarea>
                        </div>

                        <!-- Analisis & Indikator (Visual Repeater - Edit) -->
                        <div class="mb-3">
                            <label class="form-label fw-bold">Indikator Penyebab Masalah</label>
                            <p class="text-muted small mb-2">Edit indikator penyebab masalah dan analisis Anda.</p>
                            
                            <!-- Hidden field for final JSON - ID must match JS -->
                            <textarea name="analysis_data" id="hidden_analysis_data_edit" style="display:none;"><?php echo isset($step->analysis_data) ? htmlspecialchars($step->analysis_data) : ''; ?></textarea>
                            
                            <!-- Container for indicator rows - class must be .indicator-container -->
                            <div id="indicator-container-edit" class="indicator-container mb-3">
                                <!-- Rows will be generated by JavaScript based on existing data -->
                            </div>
                            
                            <!-- Add button - class must be .btn-add-indicator -->
                            <button type="button" class="btn btn-outline-success btn-sm btn-add-indicator">
                                <i class="fas fa-plus"></i> Tambah Indikator Masalah
                            </button>
                        </div>

                    </form>
                </div>
                
              </div>
              <div class="modal-footer">
                  <button id="btnUpdatedStep1" type="button" class="btn rounded-pill w-25" style="background-color: var(--custom-green); color:#ffffff">Simpan</button>
                  <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
    </div>

    <script>
    (function() {
        'use strict';
        
        function getEditRowTemplate(index) {
            return '<div class="indicator-row card mb-2 p-3 bg-white border" style="border-left: 4px solid #28a745 !important;">' +
                '<div class="d-flex justify-content-between align-items-start mb-2">' +
                    '<span class="badge bg-success row-number">Indikator #' + index + '</span>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger btn-remove-indicator-edit">' +
                        '<i class="fas fa-trash"></i> Hapus' +
                    '</button>' +
                '</div>' +
                '<div class="mb-2">' +
                    '<label class="form-label small">Indikator Penyebab</label>' +
                    '<input type="text" class="form-control indicator-cause" placeholder="Contoh: Kurangnya pemahaman siswa" required>' +
                '</div>' +
                '<div class="mb-0">' +
                    '<label class="form-label small">Analisis & Sumber Referensi</label>' +
                    '<textarea class="form-control indicator-analysis" rows="2" placeholder="Jelaskan analisis dan sumber referensi"></textarea>' +
                '</div>' +
            '</div>';
        }

        function updateEditRowNumbers() {
            var container = document.getElementById('indicator-container-edit');
            var rows = container.querySelectorAll('.indicator-row');
            rows.forEach(function(row, index) {
                var badge = row.querySelector('.row-number');
                badge.textContent = 'Indikator #' + (index + 1);
                
                var deleteBtn = row.querySelector('.btn-remove-indicator-edit');
                if (rows.length > 1) {
                    deleteBtn.style.display = '';
                } else {
                    deleteBtn.style.display = 'none';
                }
            });
        }

        window.addIndicatorRowEdit = function() {
            var container = document.getElementById('indicator-container-edit');
            var currentRows = container.querySelectorAll('.indicator-row').length;
            var newIndex = currentRows + 1;
            
            container.insertAdjacentHTML('beforeend', getEditRowTemplate(newIndex));
            updateEditRowNumbers();
            
            var newRow = container.lastElementChild;
            var deleteBtn = newRow.querySelector('.btn-remove-indicator-edit');
            deleteBtn.addEventListener('click', function() {
                removeIndicatorRowEdit(this);
            });
        };

        function removeIndicatorRowEdit(btn) {
            var container = document.getElementById('indicator-container-edit');
            var rows = container.querySelectorAll('.indicator-row');
            
            if (rows.length > 1) {
                var row = btn.closest('.indicator-row');
                row.remove();
                updateEditRowNumbers();
            }
        }

        function serializeEditIndicators() {
            var indicators = [];
            var container = document.getElementById('indicator-container-edit');
            var rows = container.querySelectorAll('.indicator-row');
            
            rows.forEach(function(row) {
                var cause = row.querySelector('.indicator-cause').value;
                var analysis = row.querySelector('.indicator-analysis').value;
                if (cause && cause.trim()) {
                    indicators.push({
                        indicator: cause.trim(),
                        analysis: analysis ? analysis.trim() : ''
                    });
                }
            });
            
            return JSON.stringify(indicators);
        }

        var modalEditElement = document.getElementById('modalEditStep1');
        modalEditElement.addEventListener('shown.bs.modal', function() {
            var container = document.getElementById('indicator-container-edit');
            var hiddenField = document.getElementById('hidden_analysis_data_edit');
            var jsonData = hiddenField.value;
            
            container.innerHTML = '';
            
            if (jsonData && jsonData.trim()) {
                try {
                    var indicators = JSON.parse(jsonData);
                    if (Array.isArray(indicators) && indicators.length > 0) {
                        indicators.forEach(function(item, index) {
                            container.insertAdjacentHTML('beforeend', getEditRowTemplate(index + 1));
                            var row = container.querySelectorAll('.indicator-row')[index];
                            row.querySelector('.indicator-cause').value = item.indicator || '';
                            row.querySelector('.indicator-analysis').value = item.analysis || '';
                            
                            row.querySelector('.btn-remove-indicator-edit').addEventListener('click', function() {
                                removeIndicatorRowEdit(this);
                            });
                        });
                        updateEditRowNumbers();
                        return;
                    }
                } catch (e) {
                    // If JSON parsing fails, show empty row
                }
            }
            
            addIndicatorRowEdit();
        });

        document.addEventListener('DOMContentLoaded', function() {
            var btnAddEdit = document.querySelector('#modalEditStep1 .btn-add-indicator');
            if (btnAddEdit) {
                btnAddEdit.addEventListener('click', addIndicatorRowEdit);
            }
            
            var btnSaveEdit = document.getElementById('btnUpdatedStep1');
            if (btnSaveEdit) {
                btnSaveEdit.addEventListener('click', function() {
                    var jsonPayload = serializeEditIndicators();
                    document.getElementById('hidden_analysis_data_edit').value = jsonPayload;
                    
                    var form = document.getElementById('formUbahStep1');
                    var formData = new FormData(form);
                    var urlEncodedData = new URLSearchParams(formData).toString();
                    
                    fetch('formeditDataStep1.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/x-www-form-urlencoded',
                        },
                        body: urlEncodedData
                    })
                    .then(function(response) {
                        return response.text();
                    })
                    .then(function(data) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Data berhasil disimpan!',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function() {
                            form.reset();
                            var modal = bootstrap.Modal.getInstance(document.getElementById('modalEditStep1'));
                            modal.hide();
                            location.reload();
                        });
                    })
                    .catch(function(error) {
                        alert('Terjadi kesalahan saat menyimpan data.');
                    });
                });
            }
        });
    })();
    </script>

    <!-- MODAL EDIT STEP 2 - JADWAL PROYEK (INLINE JS) -->
    <div class="modal fade" id="modalEditStep2" tabindex="-1" role="dialog" aria-labelledby="modalEditStep2Label" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog modal-lg" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                  <h5 class="modal-title" id="modalEditStep2Label">Edit Jadwal Proyek</h5>
              </div>
              <div class="modal-body">

                <form id="formUbahStep2" method="POST" class="p-3 border rounded bg-light">
                    <input type="hidden" name="group_project" value="<?php echo $result->groupproject; ?>">
                    <input type="hidden" name="cmid" value="<?php echo $cmid; ?>">

                    <!-- Hidden field for final JSON - pre-filled with existing data -->
                    <textarea name="planning_data" id="planning_data_edit" style="display:none;"><?php echo isset($step->planning_data) ? htmlspecialchars($step->planning_data) : ''; ?></textarea>

                    <!-- Schedule Rows Container -->
                    <div class="mb-3">
                        <label class="form-label fw-bold">Jadwal Kegiatan Proyek</label>
                        <p class="text-muted small mb-2">Edit kegiatan proyek beserta jadwal dan penanggung jawab.</p>
                        
                        <!-- Container for schedule rows - class must match JS -->
                        <div id="schedule-container-edit" class="schedule-container mb-3">
                            <!-- Rows will be generated by JavaScript based on existing data -->
                        </div>
                        
                        <!-- Add button - class must match JS -->
                        <button type="button" class="btn btn-outline-success btn-sm btn-add-schedule-edit">
                            <i class="fas fa-plus"></i> Tambah Kegiatan
                        </button>
                    </div>

                </form>
                
              </div>
              <div class="modal-footer">
                  <button id="btnUpdatedStep2" type="button" class="btn rounded-pill w-25" style="background-color: var(--custom-green); color:#ffffff">Simpan</button>
                  <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
    </div>

    <script>
    (function() {
        'use strict';
        
        function getScheduleRowTemplateEdit(index) {
            return '<div class="schedule-row card mb-2 p-3 bg-white border" style="border-left: 4px solid #17a2b8 !important;">' +
                '<div class="d-flex justify-content-between align-items-start mb-2">' +
                    '<span class="badge bg-info row-number">Kegiatan #' + index + '</span>' +
                    '<button type="button" class="btn btn-sm btn-outline-danger btn-remove-schedule-edit">' +
                        '<i class="fas fa-trash"></i> Hapus' +
                    '</button>' +
                '</div>' +
                '<div class="mb-2">' +
                    '<label class="form-label small">Nama Kegiatan</label>' +
                    '<input type="text" class="form-control schedule-task" placeholder="Contoh: Riset Awal" required>' +
                '</div>' +
                '<div class="row mb-2">' +
                    '<div class="col-md-6">' +
                        '<label class="form-label small">Tanggal Mulai</label>' +
                        '<input type="date" class="form-control schedule-start">' +
                    '</div>' +
                    '<div class="col-md-6">' +
                        '<label class="form-label small">Tanggal Selesai</label>' +
                        '<input type="date" class="form-control schedule-end">' +
                    '</div>' +
                '</div>' +
                '<div class="mb-0">' +
                    '<label class="form-label small">Penanggung Jawab (PIC)</label>' +
                    '<input type="text" class="form-control schedule-pic" placeholder="Nama anggota yang bertanggung jawab">' +
                '</div>' +
            '</div>';
        }

        function updateScheduleRowNumbersEdit() {
            var container = document.getElementById('schedule-container-edit');
            var rows = container.querySelectorAll('.schedule-row');
            rows.forEach(function(row, index) {
                var badge = row.querySelector('.row-number');
                badge.textContent = 'Kegiatan #' + (index + 1);
                
                var deleteBtn = row.querySelector('.btn-remove-schedule-edit');
                if (rows.length > 1) {
                    deleteBtn.style.display = '';
                } else {
                    deleteBtn.style.display = 'none';
                }
            });
        }

        window.addScheduleRowEdit = function() {
            var container = document.getElementById('schedule-container-edit');
            var currentRows = container.querySelectorAll('.schedule-row').length;
            var newIndex = currentRows + 1;
            
            container.insertAdjacentHTML('beforeend', getScheduleRowTemplateEdit(newIndex));
            updateScheduleRowNumbersEdit();
            
            var newRow = container.lastElementChild;
            var deleteBtn = newRow.querySelector('.btn-remove-schedule-edit');
            deleteBtn.addEventListener('click', function() {
                removeScheduleRowEdit(this);
            });
        };

        function removeScheduleRowEdit(btn) {
            var container = document.getElementById('schedule-container-edit');
            var rows = container.querySelectorAll('.schedule-row');
            
            if (rows.length > 1) {
                var row = btn.closest('.schedule-row');
                row.remove();
                updateScheduleRowNumbersEdit();
            }
        }

        function serializeScheduleEdit() {
            var schedule = [];
            var container = document.getElementById('schedule-container-edit');
            var rows = container.querySelectorAll('.schedule-row');
            
            rows.forEach(function(row) {
                var task = row.querySelector('.schedule-task').value;
                var startDate = row.querySelector('.schedule-start').value;
                var endDate = row.querySelector('.schedule-end').value;
                var pic = row.querySelector('.schedule-pic').value;
                
                if (task && task.trim()) {
                    schedule.push({
                        task: task.trim(),
                        start_date: startDate || '',
                        end_date: endDate || '',
                        pic: pic ? pic.trim() : ''
                    });
                }
            });
            
            return JSON.stringify(schedule);
        }

        // Initialize edit modal with existing data on open
        var modalEditElement = document.getElementById('modalEditStep2');
        modalEditElement.addEventListener('shown.bs.modal', function() {
            var container = document.getElementById('schedule-container-edit');
            var hiddenField = document.getElementById('planning_data_edit');
            var jsonData = hiddenField.value;
            
            container.innerHTML = '';
            
            if (jsonData && jsonData.trim()) {
                try {
                    var scheduleData = JSON.parse(jsonData);
                    if (Array.isArray(scheduleData) && scheduleData.length > 0) {
                        scheduleData.forEach(function(item, index) {
                            container.insertAdjacentHTML('beforeend', getScheduleRowTemplateEdit(index + 1));
                            var row = container.querySelectorAll('.schedule-row')[index];
                            row.querySelector('.schedule-task').value = item.task || '';
                            row.querySelector('.schedule-start').value = item.start_date || '';
                            row.querySelector('.schedule-end').value = item.end_date || '';
                            row.querySelector('.schedule-pic').value = item.pic || '';
                            
                            row.querySelector('.btn-remove-schedule-edit').addEventListener('click', function() {
                                removeScheduleRowEdit(this);
                            });
                        });
                        updateScheduleRowNumbersEdit();
                        return;
                    }
                } catch (e) {
                    // If JSON parsing fails, show empty row
                    console.log('Error parsing JSON:', e);
                }
            }
            
            // If no data or parse error, add empty row
            addScheduleRowEdit();
        });

        document.addEventListener('DOMContentLoaded', function() {
            var btnAddEdit = document.querySelector('#modalEditStep2 .btn-add-schedule-edit');
            if (btnAddEdit) {
                btnAddEdit.addEventListener('click', addScheduleRowEdit);
            }
            
            var btnSaveEdit = document.getElementById('btnUpdatedStep2');
            if (btnSaveEdit) {
                btnSaveEdit.addEventListener('click', function() {
                    var container = document.getElementById('schedule-container-edit');
                    var rows = container.querySelectorAll('.schedule-row');
                    var hasValidRow = false;
                    
                    rows.forEach(function(row) {
                        var task = row.querySelector('.schedule-task').value;
                        if (task && task.trim()) {
                            hasValidRow = true;
                        }
                    });
                    
                    if (!hasValidRow) {
                        alert('Harap tambahkan minimal satu kegiatan dengan nama kegiatan.');
                        return;
                    }
                    
                    var jsonPayload = serializeScheduleEdit();
                    document.getElementById('planning_data_edit').value = jsonPayload;
                    
                    var form = document.getElementById('formUbahStep2');
                    var formData = new FormData(form);
                    
                    fetch('formeditDataStep2.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(function(response) {
                        return response.text();
                    })
                    .then(function(data) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Jadwal berhasil diperbarui!',
                            showConfirmButton: false,
                            timer: 1500
                        }).then(function() {
                            form.reset();
                            var modal = bootstrap.Modal.getInstance(document.getElementById('modalEditStep2'));
                            modal.hide();
                            location.reload();
                        });
                    })
                    .catch(function(error) {
                        alert('Terjadi kesalahan saat menyimpan jadwal.');
                    });
                });
            }
        });
    })();
    </script>

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