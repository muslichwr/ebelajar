<link href="https://cdn.datatables.net/1.10.24/css/dataTables.bootstrap4.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@10"></script>

<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<!-- DataTables JS -->
<script src="https://cdn.datatables.net/1.10.24/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.24/js/dataTables.bootstrap4.min.js"></script>

<script>
    function lihat(kelompokId, id) {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "logaktivitasGuru.php?kelompok=" + kelompokId + "&id=" + id, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                var mainDiv = document.querySelector('div[role="main"]');
                if (mainDiv) {
                    mainDiv.innerHTML = xhr.responseText;
                }
            }
        };
        xhr.send();
    }
</script>

<script>
    function kembalimenu1(id) {
        location.reload();
    }
</script>

<script>
    function kembalimenu2(kelompokId, id) {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "logaktivitasGuru.php?kelompok=" + kelompokId + "&id=" + id, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                var mainDiv = document.querySelector('div[role="main"]');
                if (mainDiv) {
                    mainDiv.innerHTML = xhr.responseText;
                }
            }
        };
        xhr.send();
    }
</script>

<script>
    function lihatDetail(kelompokId, userId, id) {
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "logaktivitasSiswaKeGuru.php?kelompok=" + kelompokId + "&id_siswa=" + userId + "&id=" + id, true);
        xhr.onreadystatechange = function () {
            if (xhr.readyState == 4 && xhr.status == 200) {
                var mainDiv = document.querySelector('div[role="main"]');
                if (mainDiv) {
                    mainDiv.innerHTML = xhr.responseText;

                    // Inisialisasi ulang DataTable setelah load
                    $('#table').DataTable();

                    inisiasiButonTambah();

                    inisiasiButonEdit()

                    inisialisasiLihatBtn();
                }
            }
        };
        xhr.send();
    }

    function inisialisasiLihatBtn() {
        var lihatButtons = document.querySelectorAll('.lihat-btn');
        
        lihatButtons.forEach(function(button) {
            button.addEventListener('click', function() {
                var imageUrl = this.getAttribute('data-foto-url');
                var modalContent = document.querySelector('#modalLihatFoto .modal-body');
                modalContent.innerHTML = '<img src="' + imageUrl + '" class="img-fluid mx-auto d-block" alt="Foto Kegiatan">';
            });
        });
    }

    // Panggil fungsi inisialisasi event listener pada saat DOM pertama kali dimuat
    document.addEventListener('DOMContentLoaded', function() {
        inisialisasiLihatBtn();
    });

    function inisiasiButonTambah() {
        document.querySelectorAll('.btntambah').forEach(button => {
            console.log("button diklick");         
            button.addEventListener('click', function() {
                const rowId = this.getAttribute('data-id');
                const modalId = 'modaltambahFeedback_' + rowId;
                
                // Create modal dynamically
                let modalHTML = `
                    <div class="modal fade" id="${modalId}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="false" data-bs-backdrop="static">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                                    <h5 class="modal-title" id="exampleModalLabel">Tambah Feedback untuk siswa</h5>
                                </div>
                                <div class="modal-body">
                                    <form id="formTambahFeedback" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="id" value="${rowId}">
                                        <div class="form-group m-3">
                                            <label for="feedback" class="form-label fw-bold text-dark">Feedback untuk siswa</label>
                                            <textarea id="feedback" name="feedback" class="form-control shadow-sm rounded-lg p-3" 
                                                    placeholder="Tambahkan feedback untuk siswa" 
                                                    style="color:#000000; border: 1px solid #000000;" 
                                                    rows="4" required></textarea>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button id="btntambahFeedback_${rowId}" type="button" class="btn rounded-pill w-25 btntambahFeedback" style="background-color: var(--custom-green); color:#ffffff">Simpan</button>
                                    <button type="button" class="btn btn-secondary rounded-pill w-25" data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                // Insert modal into the body
                document.body.insertAdjacentHTML('beforeend', modalHTML);

                // Show the modal
                const modalElement = new bootstrap.Modal(document.getElementById(modalId));
                modalElement.show();

                // Change aria-hidden to false when modal is shown
                document.getElementById(modalId).addEventListener('shown.bs.modal', function () {
                    this.setAttribute('aria-hidden', 'false');
                });

                // Change aria-hidden to true when modal is hidden
                document.getElementById(modalId).addEventListener('hidden.bs.modal', function () {
                    this.setAttribute('aria-hidden', 'true');

                    // Remove modal after hiding to prevent duplicate modals in DOM
                    document.getElementById(modalId).remove();
                });

                // Initialize feedback button event after modal is inserted
                inisiasiTambahData(); 
            });
        });
    }

    function inisiasiButonEdit() {
        document.querySelectorAll('.btnedit').forEach(button => {
            button.addEventListener('click', function() {
                const rowId = this.getAttribute('data-id');
                const modalId = 'modaleditFeedback_' + rowId;

                const feedbackValue = this.getAttribute('data-feedback');

                let modalHTML = `
                    <div class="modal fade" id="${modalId}" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="false" data-bs-backdrop="static">
                        <div class="modal-dialog" role="document">
                            <div class="modal-content">
                                <div class="modal-header" style="background-color: var(--custom-green); color:#ffffff">
                                    <h5 class="modal-title" id="exampleModalLabel">Edit Feedback untuk siswa</h5>
                                </div>
                                <div class="modal-body">
                                    <form id="formEditFeedback_${rowId}" method="POST" enctype="multipart/form-data">
                                        <input type="hidden" name="id" value="${rowId}">
                                        <div class="form-group m-3">
                                            <label for="feedback" class="form-label fw-bold text-dark">Feedback untuk siswa</label>
                                            <textarea id="feedback" name="feedback" class="form-control shadow-sm rounded-lg p-3" 
                                                    placeholder="Edit feedback untuk siswa" 
                                                    style="color:#000000; border: 1px solid #000000;" 
                                                    rows="4" required>${feedbackValue}</textarea>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer">
                                    <button id="btneditFeedback_${rowId}" type="button" class="btn rounded-2 w-25 btneditFeedback" style="background-color: var(--custom-green); color:#ffffff">Simpan</button>
                                    <button type="button" class="btn btn-secondary rounded-2 w-25" data-bs-dismiss="modal">Tutup</button>
                                </div>
                            </div>
                        </div>
                    </div>
                `;

                document.body.insertAdjacentHTML('beforeend', modalHTML);

                const modalElement = new bootstrap.Modal(document.getElementById(modalId));
                modalElement.show();

                inisiasiTambahData();

                document.getElementById(modalId).addEventListener('hidden.bs.modal', function () {
                    this.remove();
                });
            });
        });
    }


    function inisiasiTambahData() {
        // Attach event handlers for feedback buttons using event delegation
        $(document).on('click', '.btntambahFeedback, .btneditFeedback', function() {         
            var form = $(this).closest('.modal').find('form');
            var formData = new FormData(form[0]);                   

            var url = $(this).hasClass('btntambahFeedback') ? 'formtambahFeedbackActivity.php' : 'formeditFeedbackActivity.php';

            $.ajax({
                url: url,
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
                        location.reload();
                    });
                },
                error: function(xhr, status, error) {
                    console.log("Terjadi kesalahan: " + error);
                }
            });
        });
    }

</script>



