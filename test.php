<div class="container mx-auto px-3 py-5 p-md-5" style="overflow-x: auto;">
    <table id="table" class="table table-striped table-bordered" style="width:100%">
        <thead>
            <tr>
                <th class="text-center">No</th>
                <th>Nama Kegiatan</th>
                <th>Tanggal Kegiatan</th>
                <th>Deskripsi Kegiatan</th>
                <th class="text-center">Foto Kegiatan</th>
                <th class="text-center">Feedback Guru</th>
                <?php
                    include 'koneksiPHP.php'; 
                    $user_id = isset($_SESSION["USER"]->id) ? $_SESSION["USER"]->id : null;

                    $role_id_query = "SELECT roleid FROM mdl_role_assignments WHERE userid = :userid";
                    $role_id_record = $DB->get_record_sql($role_id_query, ['userid' => $user_id]);
                    if ($role_id_result) {
                        if (mysqli_num_rows($role_id_result) > 0) {
                            $role_row = mysqli_fetch_assoc($role_id_result);

                            $roleid = $role_row['roleid'];
                    
                            if ($roleid === "3" || $roleid === "4") {
                                echo '<th class="text-center">Aksi Feedback</th>';
                            }
                        } else {
                            echo 'Tidak ada data untuk roleid';
                        }
                    } else {
                        echo "Error: " . mysqli_error($conn);
                    }
                ?>
            </tr>
        </thead>
        <tbody>
        <?php
        include 'koneksiPHP.php';
        $sql = "SELECT * FROM cst_activity_report WHERE user_id = '" . $_GET['id_siswa'] . "'";
        $result = mysqli_query($conn, $sql);
        if ($result) {
            if (mysqli_num_rows($result) > 0) {
                $counter = 1;
                while ($row = mysqli_fetch_assoc($result)) {
                    $user_id = isset($_SESSION["USER"]->id) ? $_SESSION["USER"]->id : null;

                    $role_id_query = "SELECT roleid FROM mdl_role_assignments WHERE userid='$user_id'";
                    $role_id_result = mysqli_query($conn, $role_id_query);

                    echo '
                    <tr>
                        <td class="text-center">' . $counter . '</td>
                        <td>' . $row['name_activity'] . '</td>
                        <td>' . konversiTanggal($row['date_activity']) . '</td>
                        <td>' . $row['description_activity'] . '</td>
                        <td class="text-center"><button class="btn rounded-pill lihat-btn" style="background-color: var(--custom-purple); color:#FFFFFF" data-bs-toggle="modal" data-bs-target="#modalLihatFoto" data-foto-url="' . $row['file_path'] . '">Lihat</button></td>';
                    if (!empty($row['feedback_teacher'])) {
                        echo '<td class="text-left">'. $row['feedback_teacher'] . '</td>';
                    } else {
                        echo '
                        <td class="text-center">
                            <span class="badge rounded-pill bg-danger">Tidak ada feedback dari guru</span>
                        </td>';
                    }

                    if ($role_id_result) {
                        if (mysqli_num_rows($role_id_result) > 0) {
                            $role_row = mysqli_fetch_assoc($role_id_result);

                            $roleid = $role_row['roleid'];
                    
                            if ($roleid === "3" || $roleid === "4") {
                                echo '
                                <td class="text-center">
                                    <div class="d-flex justify-content-center gap-2">';
                                if (!empty($row['feedback_teacher'])) {
                                    echo '<button class="btn rounded-pill" style="background-color: var(--custom-green); color:#FFFFFF" data-bs-toggle="modal" data-id="' . $row['id'] . '" data-bs-target="#modaleditFeedback_' . $row['id'] . '">Ubah</button>';
                                } else {
                                    echo '<button class="btn btn-danger rounded-pill" data-bs-toggle="modal" data-id="' . $row['id'] . '" data-bs-target="#modaltambahFeedback_' . $row['id'] . '">Tambahkan</button>';
                                }
                                echo '        
                                    </div>
                                </td>';
                            }
                        } else {
                            echo 'Tidak ada data untuk roleid';
                        }
                    } else {
                        echo "Error: " . mysqli_error($conn);
                    }
                        
                    echo '</tr>';

                    echo '
                    <div class="modal fade" id="modaltambahFeedback_' . $row['id'] . '" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header" style="background-color: var(--custom-yellow); color:#000000">
                                    <h5 class="modal-title" id="exampleModalLabel">Tambah Feedback untuk siswa</h5>
                                </div>
                                <div class="modal-body">
                                    <form id="formTambahFeedback" method="POST" enctype="multipart/form-data">
                                            <input type="hidden" name="id" value="'. $row['id'] .'">
                                        <div class="form-group border-0 w-75 m-2">
                                            <label for="">Feedback untuk siswa</label>
                                            <textarea  name="feedback" class="form-control rounded-lg" placeholder="Tambah feedback untuk siswa" style="background-color: rgba(255, 208, 0, 0.5); color:#000000" required></textarea>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button id="btntambahFeedback_' . $row['id'] . '" type="button" class="btn rounded-pill w-25 btntambahFeedback" style="background-color: var(--custom-yellow); color:#000000">Simpan</button>
                                    <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                    ';

                    echo '
                    <div class="modal fade" id="modaleditFeedback_' . $row['id'] . '" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header" style="background-color: var(--custom-yellow); color:#000000">
                                    <h5 class="modal-title" id="exampleModalLabel">Edit Feedback untuk siswa</h5>
                                </div>
                                <div class="modal-body">
                                    <form id="formEditFeedback" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="id" value="' . $row['id'] . '">
                                        <div class="form-group border-0 w-75 m-2">
                                            <label for="">Feedback untuk siswa</label>
                                            <textarea name="feedback" class="form-control rounded-lg" placeholder="Tambah feedback untuk siswa" style="background-color: rgba(255, 208, 0, 0.5); color:#000000" required>' . $row['feedback_teacher'] . '</textarea>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button id="btneditFeedback_' . $row['id'] . '" type="button" class="btn rounded-pill w-25 btneditFeedback" style="background-color: var(--custom-yellow); color:#000000">Simpan</button>
                                    <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>';

                    
                    $counter++;
                }
            } else {
                echo "<tr><td colspan='5' class='text-center'>Tidak ada data yang ditemukan.</td></tr>";
            }
        } else {
            echo "<tr><td colspan='5' class='text-center'>Terjadi kesalahan saat mengambil data.</td></tr>";
        }
        ?>
        </tbody>
    </table>
</div>

<div class="modal fade" id="modalLihatFoto" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header" style="background-color: var(--custom-yellow); color:#000000">
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


  <script>
      $(document).ready(function() {
          $('#table').DataTable();
      });
  </script>

<script>
    <?php if(isset($_GET['id_siswa'])) { ?>
        var idSiswaDipilih = <?php echo json_encode($_GET['id_siswa']); ?>;
        console.log("ID Siswa: " + idSiswaDipilih);
    <?php } else { ?>
        console.log("ID Siswa tidak tersedia");
    <?php } ?>
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
    $('.btntambahFeedback').click(function() {
        var form = $(this).closest('.modal').find('form');

        var formData = new FormData(form[0]);

        $.ajax({
            url: 'formtambahFeedbackActivity.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Data berhasil disimpan!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    $(this).closest('.modal').modal('hide'); // Menutup modal yang bersangkutan
                    $('#table').load(location.href + ' #table');
                    location.reload();
                });
            },
            error: function(xhr, status, error) {
                console.log("Terjadi kesalahan: " + error);
            }
        });
    });
</script>

<script>
    $('.btneditFeedback').click(function() {
        var form = $(this).closest('.modal').find('form');

        var formData = new FormData(form[0]);

        $.ajax({
            url: 'formeditFeedbackActivity.php',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: 'Data berhasil disimpan!',
                    showConfirmButton: false,
                    timer: 1500
                }).then(() => {
                    $(this).closest('.modal').modal('hide'); // Menutup modal yang bersangkutan
                    $('#table').load(location.href + ' #table');
                    location.reload();
                });
            },
            error: function(xhr, status, error) {
                console.log("Terjadi kesalahan: " + error);
            }
        });
    });
</script>