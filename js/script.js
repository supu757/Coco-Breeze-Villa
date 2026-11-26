document.addEventListener("DOMContentLoaded", function () {
    const todayStr = new Date().toISOString().split("T")[0];

    // Availability search validation on rooms.php
    const searchForm = document.getElementById("availabilityForm");
    const searchCheckIn = document.getElementById("search_check_in");
    const searchCheckOut = document.getElementById("search_check_out");
    const searchMsg = document.getElementById("searchJsMessage");

    if (searchCheckIn) {
        searchCheckIn.setAttribute("min", todayStr);
    }
    if (searchCheckOut) {
        searchCheckOut.setAttribute("min", todayStr);
    }

    if (searchForm && searchCheckIn && searchCheckOut && searchMsg) {
        searchForm.addEventListener("submit", function (e) {
            searchMsg.textContent = "";
            searchMsg.className = "js-msg";

            const inVal = searchCheckIn.value;
            const outVal = searchCheckOut.value;

            if ((inVal && !outVal) || (!inVal && outVal)) {
                e.preventDefault();
                searchMsg.textContent = "Please select both check-in and check-out dates.";
                searchMsg.classList.add("js-msg-error");
                return;
            }

            if (inVal && outVal && outVal <= inVal) {
                e.preventDefault();
                searchMsg.textContent = "Check-out date must be after the check-in date.";
                searchMsg.classList.add("js-msg-error");
            }
        });
    }

    // Live nights & total price calculation on book.php
    const bookingForm = document.getElementById("bookingForm");
    const roomSelect = document.getElementById("room_id");
    const checkInInput = document.getElementById("check_in");
    const checkOutInput = document.getElementById("check_out");
    const guestsInput = document.getElementById("guests_count");
    const summaryBox = document.getElementById("priceSummary");
    const bookingJsMsg = document.getElementById("bookingJsMessage");

    if (checkInInput) {
        checkInInput.setAttribute("min", todayStr);
    }
    if (checkOutInput) {
        checkOutInput.setAttribute("min", todayStr);
    }

    function updateBookingEstimate() {
        if (!roomSelect || !checkInInput || !checkOutInput || !summaryBox) return;

        const selectedOption = roomSelect.options[roomSelect.selectedIndex];
        const pricePerNight = parseFloat(selectedOption?.getAttribute("data-price") || "0");
        const maxGuests = parseInt(selectedOption?.getAttribute("data-max-guests") || "0", 10);

        const inDate = new Date(checkInInput.value);
        const outDate = new Date(checkOutInput.value);

        if (bookingJsMsg) {
            bookingJsMsg.textContent = "";
            bookingJsMsg.className = "js-msg";
        }

        if (guestsInput && maxGuests > 0) {
            const gVal = parseInt(guestsInput.value || "0", 10);
            if (gVal > maxGuests && bookingJsMsg) {
                bookingJsMsg.textContent = `Selected room allows a maximum of ${maxGuests} guests.`;
                bookingJsMsg.classList.add("js-msg-error");
            }
        }

        if (!checkInInput.value || !checkOutInput.value || isNaN(inDate.getTime()) || isNaN(outDate.getTime())) {
            summaryBox.innerHTML = "Select dates to see nights and estimated total.";
            return;
        }

        const diffTime = outDate.getTime() - inDate.getTime();
        const nights = Math.round(diffTime / (1000 * 60 * 60 * 24));

        if (nights <= 0) {
            summaryBox.innerHTML = "Check-out date must be after check-in date.";
            if (bookingJsMsg) {
                bookingJsMsg.textContent = "Invalid date range selected.";
                bookingJsMsg.classList.add("js-msg-error");
            }
            return;
        }

        const total = nights * pricePerNight;
        summaryBox.innerHTML = `
            <strong>Duration:</strong> ${nights} night(s)<br>
            <strong>Rate:</strong> LKR ${pricePerNight.toLocaleString("en-US", { minimumFractionDigits: 2 })}<br>
            <strong>Estimated Total:</strong> LKR ${total.toLocaleString("en-US", { minimumFractionDigits: 2 })}
        `;
    }

    if (bookingForm) {
        [roomSelect, checkInInput, checkOutInput, guestsInput].forEach((el) => {
            if (el) {
                el.addEventListener("change", updateBookingEstimate);
                el.addEventListener("input", updateBookingEstimate);
            }
        });

        updateBookingEstimate();

        bookingForm.addEventListener("submit", function (e) {
            if (!roomSelect.value || !checkInInput.value || !checkOutInput.value) {
                e.preventDefault();
                if (bookingJsMsg) {
                    bookingJsMsg.textContent = "Please select a room, check-in date, and check-out date.";
                    bookingJsMsg.classList.add("js-msg-error");
                }
                return;
            }

            const inDate = new Date(checkInInput.value);
            const outDate = new Date(checkOutInput.value);
            const nights = Math.round((outDate.getTime() - inDate.getTime()) / (1000 * 60 * 60 * 24));

            if (nights <= 0) {
                e.preventDefault();
                if (bookingJsMsg) {
                    bookingJsMsg.textContent = "Check-out date must be after check-in date.";
                    bookingJsMsg.classList.add("js-msg-error");
                }
            }
        });
    }
});

/* Sequential Hero Background Video Playlist */
document.addEventListener("DOMContentLoaded", function () {
    const heroVideo = document.getElementById("heroPlaylistVideo");
    if (!heroVideo) return;

    const playlist = [
        "assets/videos/villa_hero.mp4",
        "assets/videos/villa_interior.mp4"
    ];

    let currentIndex = 0;

    function playNextVideo() {
        currentIndex = (currentIndex + 1) % playlist.length;
        heroVideo.src = playlist[currentIndex];
        heroVideo.muted = true;
        heroVideo.load();
        const playPromise = heroVideo.play();
        if (playPromise !== undefined) {
            playPromise.catch(function (err) {
                console.error("Video play error:", err);
            });
        }
    }

    heroVideo.addEventListener("ended", playNextVideo);
    heroVideo.addEventListener("error", function () {
        console.error("Could not load video:", heroVideo.src);
    });
});
document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".room-gallery").forEach((gallery) => {
        const mainImg = gallery.querySelector(".room-gallery-active");
        const thumbs = Array.from(gallery.querySelectorAll(".room-thumb"));
        const prevBtn = gallery.querySelector(".room-nav.prev");
        const nextBtn = gallery.querySelector(".room-nav.next");

        if (!mainImg || thumbs.length === 0) return;

        let currentIndex = 0;

        const showImage = (index) => {
            currentIndex = (index + thumbs.length) % thumbs.length;
            const activeThumb = thumbs[currentIndex];
            mainImg.src = activeThumb.dataset.src;

            thumbs.forEach((t, i) => {
                t.classList.toggle("active", i === currentIndex);
            });
        };

        thumbs.forEach((thumb, idx) => {
            thumb.addEventListener("click", () => showImage(idx));
        });

        if (prevBtn) {
            prevBtn.addEventListener("click", () => showImage(currentIndex - 1));
        }

        if (nextBtn) {
            nextBtn.addEventListener("click", () => showImage(currentIndex + 1));
        }
    });
});