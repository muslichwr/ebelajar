<?php

require_once('../../config.php');

redirect_if_major_upgrade_required();

require_login();

global $DB, $USER;

$user_id = $USER->id;

$cmid = required_param('id', PARAM_INT);

if (!$cm) {
    print_error('invalidcoursemodule');
}
$ebelajar = $DB->get_field('ebelajar', 'id', ['coursemoduleid' => $cmid]);
$groupproject_records = $DB->get_records('groupproject', ['ebelajar' => $ebelajar]);
$total_grup = $DB->get_field('ebelajar', 'total_grup', ['coursemoduleid' => $cmid]);
$step3_schedule_image = $DB->get_field('ebelajar', 'step3_schedule_image', ['coursemoduleid' => $cmid]);
$step3_schedule_file = $DB->get_field('ebelajar', 'step3_schedule_file', ['coursemoduleid' => $cmid]);

?>

<?php
    include 'logicscript.php';
  ?>

<style>
    :root {
        --custom-blue: #bed4d1;
        --custom-green: #5a9f68;
        --custom-red: #ff5757;
    }
    .logo {
        display: inline-block;
    }
</style>

<script>
    $(document).ready(function() {
        $('#btnSimpanStep3').click(function() {
            var formData = new FormData($('#formTambahDataStep3')[0]);
            var file_step3 = $('[name="file_step3"]').prop('files')[0];
            var file_image = $('[name="file_image"]').prop('files')[0];

            if (!file_step3 || !file_image) {
                alert("Harap lengkapi semua bidang.");
                return;
            }
            
            $.ajax({
                url: 'formtambahDataStep3.php',
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
                        $('#formTambahDataStep3')[0].reset();
                        $('#modalTambahStep3').modal('hide');
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
        $('#btnUpdateStep3').click(function() {
            var form = $(this).closest('.modal').find('form');

            var formData = new FormData(form[0]);
            
            $.ajax({
                url: 'formeditDataStep3.php',
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
                        $('#formEditDataStep3')[0].reset();
                        $('#modalEditStep3').modal('hide');
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

    <nav class="container navbar navbar-expand-md navbar-light px-2 rounded-2" style="background-color: var(--custom-green); border: 4px solid var(--custom-blue);">
        <div class="container-fluid px-md-5">
            <div class="logo mx-auto">
                <h3 class="navbar-brand text-white fw-bolder">Activityku</h3>
            </div>
        </div>
    </nav>

    <div class="container mx-auto p-3">
        <div class="row align-items-center border rounded-2 p-3" style="background-color: var(--custom-green);">
            <!-- Kolom teks -->
            <div class="col-lg-6 col-12 text-center text-lg-start mb-4 mb-lg-0">
                <h3 class="fw-bolder text-white">Pilih Kelompok</h3>
                <p class="text-white">Silahkan memilih kelompok untuk melihat aktivitas kelompok yang telah dilakukan dalam proses pembelajaran.</p>
            </div>
            <!-- Kolom gambar -->
            <div class="col-lg-6 col-12 text-center">
                <img src="assets/guru-awal(2).svg" class="img-fluid" alt="Ilustrasi">
            </div>
        </div>
    </div>

    <div class="container mx-auto p-3" id="dataProjectContainer">
        <div class="row">
            <div class="col-12">
                <?php if ($step3_schedule_image && $step3_schedule_file != null): ?>
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
                        <div class="card-footer" style="background-color: var(--custom-red);">
                            <button data-bs-toggle="modal" data-bs-target="#modalEditStep3" class="btn font-bolder" style="background-color: var(--custom-blue); color: var(--custom-green);"><strong>Edit Data</strong></button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="d-flex justify-content-end mb-2">
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#modalTambahStep3"><i class="fas fa-plus"></i>Tambah Data</button>
                    </div>
                    <div class="alert alert-warning">
                        Anda belum menambahkan langkah 3 untuk penjadwalan siswa.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>


    <div class="modal fade" id="modalTambahStep3" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                  <h5 class="modal-title" id="exampleModalLabel">Data Step 3</h5>
              </div>
              <div class="modal-body">
                <form id="formTambahDataStep3" method="POST" enctype="multipart/form-data" class="p-4 border rounded bg-light">
                    <input type="hidden" name="id" value="<?php echo $cmid; ?>">
                    <!-- File image -->
                    <div class="mb-3">
                        <label for="file_image" class="form-label">Gambar Thumnail Jadwal</label>
                        <input type="file" id="file_image" name="file_image" 
                            class="form-control rounded-2 px-3 py-2" 
                            style="color:#000000;">
                    </div>

                    <!-- File Step3 -->
                    <div class="mb-3">
                        <label for="file_step3" class="form-label">File Lampiran</label>
                        <input type="file" id="file_step3" name="file_step3" 
                            class="form-control rounded-2 px-3 py-2" 
                            style="color:#000000;">
                    </div>
                </form>
              </div>
              <div class="modal-footer">
                  <button id="btnSimpanStep3" type="button" class="btn rounded-pill w-25" style="background-color: var(--custom-green); color:#ffffff">Simpan</button>
                  <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
    </div>

    <div class="modal fade" id="modalEditStep3" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                  <h5 class="modal-title" id="exampleModalLabel">Data Step 3</h5>
              </div>
              <div class="modal-body">
                <form id="formEditDataProject" method="POST" enctype="multipart/form-data" class="p-4 border rounded bg-light">
                    <input type="hidden" name="id" value="<?php echo $cmid; ?>">

                    <!-- File Gambar -->
                    <div class="mb-3">
                        <label for="file_image" class="form-label">Gambar Thumnail Jadwal</label>
                        <input type="file" id="file_image" name="file_image" 
                            class="form-control rounded-2 px-3 py-2" 
                            style="color:#000000;">
                    </div>

                    <!-- File Step3 -->
                    <div class="mb-3">
                        <label for="file_step3" class="form-label">File Lampiran</label>
                        <input type="file" id="file_step3" name="file_step3" 
                            class="form-control rounded-2 px-3 py-2" 
                            style="color:#000000;">
                    </div>
                </form>
              </div>
              <div class="modal-footer">
                  <button id="btnUpdateStep3" type="button" class="btn rounded-pill w-25" style="background-color: var(--custom-green); color:#ffffff">Update</button>
                  <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
              </div>
          </div>
      </div>
    </div>

    <?php
    foreach ($groupproject_records as $groupproject) {
        // Ambil ID grup proyek
        $group_project = $groupproject->id;

        // Ambil status langkah proyek
        $step1_status = $DB->get_field('project', 'status_step1', ['group_project' => $group_project]);
        $step2_status = $DB->get_field('project', 'status_step2', ['group_project' => $group_project]);
        $step3_status = $DB->get_field('project', 'status_step3', ['group_project' => $group_project]);
        $step4_status = $DB->get_field('project', 'status_step4', ['group_project' => $group_project]);
        $step5_status = $DB->get_field('project', 'status_step5', ['group_project' => $group_project]);
        $step6_status = $DB->get_field('project', 'status_step6', ['group_project' => $group_project]);
        $step7_status = $DB->get_field('project', 'status_step7', ['group_project' => $group_project]);
        $step8_status = $DB->get_field('project', 'status_step8', ['group_project' => $group_project]);

        // Hitung jumlah langkah yang selesai
        $completed_steps = 0;
        if ($step1_status == "Selesai") $completed_steps++;
        if ($step2_status == "Selesai") $completed_steps++;
        if ($step3_status == "Selesai") $completed_steps++;
        if ($step4_status == "Selesai") $completed_steps++;
        if ($step5_status == "Selesai") $completed_steps++;
        if ($step6_status == "Selesai") $completed_steps++;
        if ($step7_status == "Selesai") $completed_steps++;
        if ($step8_status == "Selesai") $completed_steps++;

        // Hitung total langkah
        $total_steps = 8;

        $percentage = ($completed_steps / $total_steps) * 100;

        echo '
        <div class="container mx-auto p-3">
            <div class="row align-items-center border rounded-2 p-3" style="background-color: var(--custom-blue);">
                <!-- Kolom teks -->
                <div class="col-6 col-sm-9 text-start ">
                    <div class="d-flex align-items-center px-3">
                        <h4 class="fw-bold" style="color: var(--custom-green);">Kelompok ' . $groupproject->group_number . '</h4>
                    </div>
                    <div class="fw-bold fs-5 px-3" style="color: #000000;">
                        <span>Progress: ' . $completed_steps . '/' . $total_steps . '</span>
                    </div>
                    <div class="progress" style="height: 20px;">
                        <div class="progress-bar" role="progressbar" style="width: ' . $percentage . '%; background-color: var(--custom-green);" aria-valuenow="' . $percentage . '" aria-valuemin="0" aria-valuemax="100">
                            ' . round($percentage) . '% 
                        </div>
                    </div>
                </div>
                <div class="col-3 text-center">
                    <div class="border-0 border-dark px-3 py-2 w-125 text-center">
                        <button class="btn text-white" style="background-color: var(--custom-red);" onclick="lihat(' . $groupproject->id . ', ' . $cmid . ')">Lihat Kelompok</button>
                    </div>
                </div>
            </div>
        </div>
        ';
    }
    ?>
