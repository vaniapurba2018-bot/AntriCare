
document.addEventListener('DOMContentLoaded', () => {


    const showToast = (message, type = 'success') => {
        console.log(message); 
  
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        document.body.appendChild(toast);
        setTimeout(() => toast.classList.add('show'), 10);
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => document.body.removeChild(toast), 300);
        }, 3000);
    };

    const loggedInUserString = localStorage.getItem('loggedInUser');
    if (loggedInUserString) {
        const loggedInUser = JSON.parse(loggedInUserString);
        const navAuth = document.getElementById('nav-auth');
        if (navAuth) {
      
   navAuth.innerHTML = `
    <span class="welcome-user">Halo, <a href="profil.php">${loggedInUser.name}</a>!</span>
    <a href="#" id="logoutButton" class="btn btn-login">Logout</a>
`;
        }

        const heroSignupButton = document.getElementById('hero-signup-button');
        if (heroSignupButton) heroSignupButton.style.display = 'none';

        const logoutButton = document.getElementById('logoutButton');
        if (logoutButton) {
            logoutButton.addEventListener('click', (event) => {
                event.preventDefault();
                fetch('logout.php')
                .then(response => response.json())
                .then(data => {
                    localStorage.removeItem('loggedInUser');
                    localStorage.removeItem('antrianData');
                    showToast('Anda telah berhasil logout.');
                    setTimeout(() => window.location.href = 'index.html', 1000);
                })
                .catch(error => {
                    localStorage.removeItem('loggedInUser');
                    localStorage.removeItem('antrianData');
                    window.location.href = 'index.html';
                });
            });
        }
    }

    const signupForm = document.getElementById('signupForm');
    if (signupForm) {
        signupForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const name = document.getElementById('name').value;
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            fetch('register.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ name, email, password })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message + ' Silakan login.');
                    setTimeout(() => window.location.href = 'login.html', 1500);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => showToast('Terjadi kesalahan jaringan.', 'error'));
        });
    }


    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
            event.preventDefault();
            const email = document.getElementById('email').value;
            const password = document.getElementById('password').value;

            fetch('login.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    localStorage.setItem('loggedInUser', JSON.stringify({ name: data.user.name }));
                    showToast(data.message + ' Selamat datang!');

              
                    const urlParams = new URLSearchParams(window.location.search);
                    const redirectUrl = urlParams.get('redirect_url');
                    let destination = ''; 
                    
                    if (redirectUrl) {
                        destination = decodeURIComponent(redirectUrl);
                    } else {
                        const userRole = data.user.role; 
                        if (userRole === 'admin') {
                            destination = 'admin.php'; 
                        } else {
                            destination = 'index.html'; 
                        }
                    }
                    setTimeout(() => window.location.href = destination, 1500);

                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => { 
                showToast('Terjadi kesalahan jaringan.', 'error');
                console.error('Error:', error);
            });

        }); 
    }
    const antrianForm = document.getElementById('antrianForm');
    if (antrianForm) {
        antrianForm.addEventListener('submit', function(e) {
            e.preventDefault();
            const nikInput = document.getElementById('nik');
            if (nikInput.value.length !== 16) {
                showToast('NIK harus terdiri dari tepat 16 digit angka.', 'error');
                return;
            }

            const formData = new FormData(antrianForm);
            fetch('ambil_antrian.php', { method: 'POST', body: formData })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message);
                    localStorage.setItem('antrianData', JSON.stringify(data.ticketData));
                    setTimeout(() => { window.location.href = 'nomor.html'; }, 1500);
                } else {
                    showToast(data.message, 'error');
                }
            })
            .catch(error => showToast('Terjadi kesalahan. Coba lagi.', 'error'));
        });
    }

 
    const antrianListBody = document.getElementById('antrian-list-body');
    if (antrianListBody) {
        fetch('get_status.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const antrian = data.queues.filter(q => q.status === 'waiting');
                if (antrian.length > 0) {
                    antrian.forEach((patient, index) => {
                        const row = document.createElement('tr');
                        row.innerHTML = `
                            <td>${index + 1}</td>
                            <td>${patient.nama_pasien}</td>
                            <td>${patient.poli}</td>
                            <td>${patient.nomor_antrian}</td>
                        `;
                        antrianListBody.appendChild(row);
                    });
                } else {
                    antrianListBody.innerHTML = `<tr><td colspan="4" style="text-align:center;">Tidak ada pasien dalam antrian.</td></tr>`;
                }
            } else {
                 antrianListBody.innerHTML = `<tr><td colspan="4" style="text-align:center;">Gagal memuat data: ${data.message}</td></tr>`;
            }
        })
        .catch(error => antrianListBody.innerHTML = `<tr><td colspan="4" style="text-align:center;">Terjadi kesalahan jaringan.</td></tr>`);
    }


    const nomorAntrianEl = document.getElementById('nomorAntrian');
    if (nomorAntrianEl) {
        const data = JSON.parse(localStorage.getItem('antrianData'));
        if (data) {
            document.getElementById('nomorAntrian').textContent = data.nomor;
            document.getElementById('namaPasien').textContent = data.nama;
            document.getElementById('poliTujuan').textContent = data.poli;
            document.getElementById('perkiraanJam').textContent = data.jam;
        }
        const cetakBtn = document.getElementById('cetakBtn');
        if (cetakBtn) cetakBtn.addEventListener('click', () => { window.print(); });
    }

    
    const adminDashboard = document.getElementById('admin-dashboard');
    if (adminDashboard) {
        const renderAdminDashboard = () => {
            fetch('get_status.php')
            .then(response => response.json())
            .then(data => {
                if (!data.success) { showToast(data.message, 'error'); return; }

                const queues = data.queues;
                const serving = data.serving;
                adminDashboard.innerHTML = '';
                const poliList = ["Poli Umum", "Poli Gigi", "Poli KIA", "Poli KB", "Poli Lansia", "Poli Gizi"];

                poliList.forEach(poli => {
                    const nowServing = serving[poli];
                    const nextPatient = queues.find(p => p.poli === poli && p.status === 'waiting');
                    const card = document.createElement('div');
                    card.className = 'admin-card';
                    card.innerHTML = `
                        <h3>${poli}</h3>
                        <div class="current-serving">Sedang Dilayani: <span class="current-number">${nowServing ? nowServing.nomor_antrian : '---'}</span></div>
                        <div class="next-in-line">Berikutnya: ${nextPatient ? nextPatient.nomor_antrian + ' - ' + nextPatient.nama_pasien : 'Tidak ada'}</div>
                        <button class="btn-call-next" data-poli="${poli}" ${!nextPatient ? 'disabled' : ''}>Panggil Berikutnya</button>
                    `;
                    adminDashboard.appendChild(card);
                });
            })
            .catch(error => console.error('Error fetching admin status:', error));
        };

        renderAdminDashboard();

        adminDashboard.addEventListener('click', (e) => {
            if (e.target.classList.contains('btn-call-next')) {
                const poli = e.target.dataset.poli;
                fetch(`admin_action.php?action=call_next&poli=${poli}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showToast(data.message);
                        renderAdminDashboard();
                    } else {
                        showToast(data.message, 'error');
                    }
                })
                .catch(error => console.error('Error calling next:', error));
            }
        });
    }

  
    if (document.getElementById('profile-form')) {
        fetch('get_profile.php')
        .then(response => response.json())
        .then(data => {
            if (!data.success) { showToast(data.message, 'error'); return; }

            document.getElementById('profile-name').value = data.user.name;
            document.getElementById('profile-email').value = data.user.email;
            document.getElementById('profile-form').addEventListener('submit', (e) => {
                e.preventDefault();
                showToast('Fitur "Ubah Password" belum tersedia.', 'info');
            });

            const activeQueueLoading = document.getElementById('active-queue-loading');
            const activeQueueDetails = document.getElementById('active-queue-details');
            const noActiveQueue = document.getElementById('no-active-queue');
            const activeTicket = data.active_ticket;

            activeQueueLoading.style.display = 'none';
            if (activeTicket) {
                activeQueueDetails.style.display = 'block';
                document.getElementById('active-poli').textContent = activeTicket.poli;
                document.getElementById('active-nomor').textContent = activeTicket.nomor_antrian;
                document.getElementById('profile-nik').value = activeTicket.nik;
            } else {
                noActiveQueue.style.display = 'block';
            }

            const historyTableBody = document.getElementById('history-table-body');
            const historyLoadingRow = document.getElementById('history-loading-row');
            historyLoadingRow.style.display = 'none';

            const userHistory = data.history;
            if (userHistory && userHistory.length > 0) {
                userHistory.forEach(visit => {
                    const row = document.createElement('tr');
                    const visitDate = new Date(visit.timestamp).toLocaleDateString('id-ID', {
                        day: '2-digit', month: 'long', year: 'numeric'
                    });
                    row.innerHTML = `<td>${visitDate}</td><td>${visit.poli}</td><td>${visit.nomor_antrian}</td>`;
                    historyTableBody.appendChild(row);
                });
                if (!activeTicket) {
                    document.getElementById('profile-nik').value = userHistory[0].nik;
                }
            } else {
                historyTableBody.innerHTML = `<tr><td colspan="3" style="text-align: center;">Belum ada riwayat kunjungan.</td></tr>`;
            }
        })
        .catch(error => console.error('Error fetching profile:', error));
    }

  
    const jadwalTableBody = document.getElementById('jadwal-table-body');
    if (jadwalTableBody) {
        const today = new Date().toLocaleDateString('id-ID', {
            weekday: 'long', day: 'numeric', month: 'long', year: 'numeric'
        });
        document.getElementById('nama-hari-ini').textContent = today;

        fetch('get_jadwal.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    const loadingRow = document.getElementById('jadwal-loading');
                    if (loadingRow) loadingRow.remove();

                    data.jadwal.forEach(item => {
    const row = document.createElement('tr');
    

    const isBuka = item.status === 'Buka';
    
 
    const statusClass = isBuka ? 'status-buka' : 'status-tutup';
    const jamOperasional = isBuka ? `${item.jam_buka} - ${item.jam_tutup}` : 'Tutup';
    
 
    const btnHref = isBuka ? 'antrian.php' : '#';
    const btnClass = isBuka ? 'btn btn-primary btn-sm' : 'btn btn-primary btn-sm disabled';
    const btnStyle = isBuka ? '' : 'pointer-events: none; opacity: 0.6;'; 
    const btnAttr = isBuka ? '' : 'tabindex="-1" aria-disabled="true"';

   
    row.innerHTML = `
        <td><strong>${item.poli}</strong></td>
        <td>${item.dokter}</td>
        <td>${jamOperasional}</td>
        <td><span class="status ${statusClass}">${item.status}</span></td>
        <td>
            <a href="${btnHref}" class="${btnClass}" style="${btnStyle}" ${btnAttr}>Ambil Antrian</a>
        </td>
    `;
    
    jadwalTableBody.appendChild(row);
});
                } else {
                    jadwalTableBody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: red;">Gagal memuat jadwal: ${data.message}</td></tr>`;
                }
            })
            .catch(error => jadwalTableBody.innerHTML = `<tr><td colspan="5" style="text-align: center; color: red;">Terjadi kesalahan jaringan.</td></tr>`);
    }


    const dropbtn = document.querySelector('.dropbtn');
    if (dropbtn) {
        dropbtn.addEventListener('click', function(event) {
            event.preventDefault();
            document.getElementById("poliDropdown").classList.toggle("show");
        });
    }
    window.addEventListener('click', function(event) {
        if (!event.target.matches('.dropbtn')) {
            var dropdowns = document.getElementsByClassName("dropdown-content");
            for (var i = 0; i < dropdowns.length; i++) {
                var openDropdown = dropdowns[i];
                if (openDropdown.classList.contains('show')) {
                    openDropdown.classList.remove('show');
                }
            }
        }
    });

    const slider = document.querySelector('.testimonial-slider');
    const prevButton = document.getElementById('prev-slide');
    const nextButton = document.getElementById('next-slide');
    if (slider && prevButton && nextButton) {
        nextButton.addEventListener('click', () => slider.scrollBy({ left: slider.clientWidth, behavior: 'smooth' }));
        prevButton.addEventListener('click', () => slider.scrollBy({ left: -slider.clientWidth, behavior: 'smooth' }));
    }

    const eyeIcon = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/></svg>`;
    const eyeSlashIcon = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path d="M12 7c2.76 0 5 2.24 5 5 0 .65-.13 1.26-.36 1.83l2.92 2.92c1.51-1.26 2.7-2.89 3.44-4.75-1.73-4.39-6-7.5-11-7.5-1.4 0-2.74.25-3.98.7l2.16 2.16C10.74 7.13 11.35 7 12 7zM2 4.27l2.28 2.28.46.46C3.08 8.3 1.78 10.02 1 12c1.73 4.39 6 7.5 11 7.5 1.55 0 3.03-.3 4.38-.84l.42.42L19.73 22 21 20.73 3.27 3 2 4.27zM7.53 9.8l1.55 1.55c-.05.21-.08.43-.08.65 0 1.66 1.34 3 3 3 .22 0 .44-.03.65-.08l1.55 1.55c-.67.33-1.41.53-2.2.53-2.76 0-5-2.24-5-5 0-.79.2-1.53.53-2.2zm4.31-.78l3.15 3.15.02-.16c0-1.66-1.34-3-3-3l-.17.01z"/></svg>`;
    const togglePasswordIcons = document.querySelectorAll('.toggle-password');
    togglePasswordIcons.forEach(icon => {
        icon.innerHTML = eyeSlashIcon;
        icon.addEventListener('click', function () {
            const passwordInput = this.previousElementSibling;
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            this.innerHTML = isPassword ? eyeIcon : eyeSlashIcon;
        });
    });
// ... (KODE ANDA SEBELUMNYA TETAP DI SINI) ...
    // (Misalnya di bawah logika togglePasswordIcons)

}); // <-- INI ADALAH KURUNG PENUTUP TERAKHIR DARI document.addEventListener
