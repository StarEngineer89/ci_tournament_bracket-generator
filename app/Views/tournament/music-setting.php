<!-- Music during the shuffling -->
<div class="music-setting p-2 mb-1">
    <div class="form-check border-bottom mb-3">
        <input type="checkbox" class="form-check-input toggle-music-settings" name="setting-toggle[0]" id="toggle-music-settings-0" onChange="musicSettingToggleChange(this)">
        <label class="form-check-label" for="toggle-music-settings-0">
            <h6>Music during the generation</h6>
        </label>
    </div>

    <div class="setting visually-hidden">
        <input type="hidden" name="audioType[0]" value="0">
        <div class="input-group mb-3">
            <div class="input-group-text">
                <input class="form-check-input mt-0" type="radio" value="f" aria-label="Radio button for following text input" name="source[0]" onChange="musicSourceChange(this)" data-target="file" checked>
            </div>
            <input type="file" class="form-control music-source" data-source="file" name="file" onChange="musicFileUpload(this)" accept="audio/mpeg,audio/wav,audio/ogg,audio/mid,audio/x-midi" required disabled>
            <label class="input-group-text">Upload</label>
            <input type="hidden" class="file-path" id="file-shuffling-music" name="file-path[0]">
        </div>
        <div class="input-group mb-3">
            <div class="input-group-text">
                <input class="form-check-input mt-0" type="radio" value="y" aria-label="Radio button for following text input" name="source[0]" onChange="musicSourceChange(this)" data-target="url">
            </div>
            <span class="input-group-text">URL</span>
            <input type="text" class="form-control music-source" data-source="url" aria-describedby="basic-addon3 basic-addon4" name="url[0]" required disabled>
            <div class="form-text">Example: <a href="Example: https://youtu.be/Gb1iGDchKYs?si=nR-n7KBMHfKWox7U">https://youtu.be/Gb1iGDchKYs?si=nR-n7KBMHfKWox7U</a></div>
            <div class="invalid-feedback">This field is required.</div>
        </div>
        <div class="mb-3 preview">
            <audio controls class="w-100 player">
                <source class="playerSource" src="" type="audio/mpeg" />
            </audio>

            <div class="row g-3">
                <div class="col-6">
                    <div class="input-group">
                        <div class="input-group-text">Start</div>
                        <input type="text" class="form-control time startAt" onChange="musicDurationChange(this)" placeholder="00:00:00" pattern="^([0-1][0-9]|[2][0-3]):([0-5][0-9]):([0-5][0-9])$" required disabled>
                        <input type="hidden" class="form-control form-control-sm startAt" name="start[0]">
                    </div>
                </div>

                <div class="col-6">
                    <div class="input-group">
                        <div class="input-group-text">Stop</div>
                        <input type="text" class="form-control time stopAt" onChange="musicDurationChange(this)" placeholder="00:00:00" pattern="^([0-1][0-9]|[2][0-3]):([0-5][0-9]):([0-5][0-9])$" required disabled>
                        <input type="hidden" class="form-control form-control-sm stopAt" name="stop[0]">
                    </div>
                </div>

                <input type="hidden" class="duration" name="duration[0]" value="5">
            </div>
        </div>
    </div>
</div>

<!-- Music for the Final Winner -->
<div class="music-setting p-2 mb-1">
    <div class="form-check border-bottom mb-3">
        <input type="checkbox" class="form-check-input toggle-music-settings" name="setting-toggle[1]" id="toggle-music-settings-1" onChange="musicSettingToggleChange(this)">
        <label class="form-check-label" for="toggle-music-settings-1">
            <h6>Music for a Final Winner</h6>
        </label>
    </div>

    <div class="setting visually-hidden">
        <input type="hidden" name="audioType[1]" value="1">
        <div class="input-group mb-3">
            <div class="input-group-text">
                <input class="form-check-input mt-0" type="radio" value="f" aria-label="Radio button for following text input" name="source[1]" onChange="musicSourceChange(this)" data-target="file" checked>
            </div>
            <input type="file" class="form-control music-source" data-source="file" name="file" onChange="musicFileUpload(this)" accept="audio/mpeg,audio/wav,audio/ogg,audio/mid,audio/x-midi" required disabled>
            <label class="input-group-text">Upload</label>
            <input type="hidden" class="file-path" id="file-input" name="file-path[1]">
            <div class="invalid-feedback">This field is required.</div>
        </div>
        <div class="input-group mb-3">
            <div class="input-group-text">
                <input class="form-check-input mt-0" type="radio" value="y" aria-label="Radio button for following text input" name="source[1]" onChange="musicSourceChange(this)" data-target="url">
            </div>
            <span class="input-group-text">URL</span>
            <input type="text" class="form-control music-source" data-source="url" aria-describedby="basic-addon3 basic-addon4" name="url[1]" required disabled>
            <div class="form-text">"Example: <a href="Example: https://youtu.be/Gb1iGDchKYs?si=nR-n7KBMHfKWox7U">https://youtu.be/Gb1iGDchKYs?si=nR-n7KBMHfKWox7U</a>"</div>
            <div class="invalid-feedback">This field is required.</div>
        </div>
        <div class="mb-3 preview">
            <audio controls class="w-100 player">
                <source class="playerSource" src="" type="audio/mpeg" />
            </audio>

            <div class="row g-3">
                <div class="col-6">
                    <div class="input-group">
                        <div class="input-group-text">Start</div>
                        <input type="text" class="form-control time startAt" onChange="musicDurationChange(this)" placeholder="00:00:00" pattern="^([0-1][0-9]|[2][0-3]):([0-5][0-9]):([0-5][0-9])$" required disabled>
                        <input type="hidden" class="form-control form-control-sm startAt" name="start[1]">
                    </div>
                </div>

                <div class="col-6">
                    <div class="input-group">
                        <div class="input-group-text">Stop</div>
                        <input type="text" class="form-control time stopAt" onChange="musicDurationChange(this)" placeholder="00:00:00" pattern="^([0-1][0-9]|[2][0-3]):([0-5][0-9]):([0-5][0-9])$" required disabled>
                        <input type="hidden" class="form-control form-control-sm stopAt" name="stop[1]">
                    </div>
                </div>

                <input type="hidden" class="duration" name="duration[1]" value="5">
            </div>
        </div>
    </div>
</div>