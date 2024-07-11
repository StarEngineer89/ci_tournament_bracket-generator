let toggleShuffleParticipants = (checkbox) => {
    var enableShufflingHint = document.querySelector('.enable-shuffling-hint');
    var disableShufflingHint = document.querySelector('.disable-shuffling-hint');

    if (checkbox.checked) {
        enableShufflingHint.classList.remove('d-none');
        disableShufflingHint.classList.add('d-none');
    } else {
        enableShufflingHint.classList.add('d-none');
        disableShufflingHint.classList.remove('d-none');
    }
}

let stopMusicPlaying = () => {
    // Your code to stop music goes here
    const audio = document.getElementById('myAudio');

    if (audio.paused) {
        audio.play();
        document.getElementById('stopMusicButton').textContent = "Pause Music"
    } else {
        audio.pause();
        document.getElementById('stopMusicButton').textContent = "Resume Music"
    }
}