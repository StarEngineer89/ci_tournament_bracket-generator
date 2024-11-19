<!-- Music during the shuffling -->
<div class="music-setting p-2 mb-1">
    <div class="form-check border-bottom mb-3">
        <input type="checkbox" class="form-check-input toggle-music-settings" name="setting-toggle[0]" id="toggle-music-settings-0" onChange="musicSettingToggleChange(this)">
        <label class="form-check-label" for="toggle-music-settings-0">
            <h6>Music during Brackets Generation</h6>
        </label>
    </div>

    <div class="setting visually-hidden">
        <input type="hidden" name="audioType[0]" value="0">
        <div class="input-group mb-3">
            <div class="input-group-text">
                <input class="form-check-input mt-0" type="radio" value="f" aria-label="Radio button for following text input" name="source[0]" onChange="musicSourceChange(this)" data-target="file" checked>
            </div>
            <input type="file" class="form-control music-source" data-source="file" name="file" onChange="musicFileUpload(this)" accept="audio/mpeg,audio/wav,audio/ogg,audio/mid,audio/x-midi" required disabled>
            <input type="hidden" class="file-path" id="file-shuffling-music" name="file-path[0]">
            <div class="fileupload-hint form-text">Select a .mp3 file to upload. After waiting a bit, you will notice the player loads the file here when the timestamps appear, in which you could then adjust accordingly.</div>
        </div>
        <div class="input-group mb-3">
            <div class="input-group-text">
                <input class="music-url-enable form-check-input mt-0" type="radio" value="y" aria-label="Radio button for following text input" name="source[0]" onChange="musicSourceChange(this)" data-target="url">
            </div>
            <span class="input-group-text">URL</span>
            <input type="text" class="form-control music-source" data-source="url" aria-describedby="basic-addon3 basic-addon4" name="url[0]" placeholder="Example: https://youtu.be/Gb1iGDchKYs?si=nR-n7KBMHfKWox7U" required disabled>
            <div class="invalid-feedback">This field is required.</div>
            <div class="urlupload-hint form-text d-none">Enter a YouTube video url. <br />Note an <a href="https://developers.google.com/youtube/v3/guides/implementation/videos">API</a> will process the video into a .mp3 file in the backend once you click save. You may specify the timestamps before saving or you may revise the timestamps by navigating to the tournament's music settings action after it's generated.</div>
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
                    <div class="invalid-feedback d-none" id="start-time-error-0">Start time must be less than stop time.</div>
                </div>

                <div class="col-6">
                    <div class="input-group">
                        <div class="input-group-text">Stop</div>
                        <input type="text" class="form-control time stopAt" onChange="musicDurationChange(this)" placeholder="00:00:00" pattern="^([0-1][0-9]|[2][0-3]):([0-5][0-9]):([0-5][0-9])$" required disabled>
                        <input type="hidden" class="form-control form-control-sm stopAt" name="stop[0]">
                    </div>
                    <div class="invalid-feedback d-none" id="stop-time-error-0">Stop time must be greater than start time.</div>
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
            <input type="hidden" class="file-path" id="file-input" name="file-path[1]">
            <div class="invalid-feedback">This field is required.</div>
            <div class="fileupload-hint form-text">Select a .mp3 file to upload. After waiting a bit, you will notice the player loads the file here when the timestamps appear, in which you could then adjust accordingly.</div>
        </div>
        <div class="input-group mb-3">
            <div class="input-group-text">
                <input class="music-url-enable form-check-input mt-0" type="radio" value="y" aria-label="Radio button for following text input" name="source[1]" onChange="musicSourceChange(this)" data-target="url">
            </div>
            <span class="input-group-text">URL</span>
            <input type="text" class="form-control music-source" data-source="url" aria-describedby="basic-addon3 basic-addon4" name="url[1]" placeholder="Example: https://youtu.be/Gb1iGDchKYs?si=nR-n7KBMHfKWox7U" required disabled>
            <div class="invalid-feedback">This field is required.</div>
            <div class="urlupload-hint form-text d-none">Enter a YouTube video url. <br />Note an <a href="https://developers.google.com/youtube/v3/guides/implementation/videos">API</a> will process the video into a .mp3 file in the backend once you click save. You may specify the timestamps before saving or you may revise the timestamps by navigating to the tournament's music settings action after it's generated.</div>
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
                    <div class="invalid-feedback d-none" id="start-time-error-1">Start time must be less than stop time.</div>
                </div>

                <div class="col-6">
                    <div class="input-group">
                        <div class="input-group-text">Stop</div>
                        <input type="text" class="form-control time stopAt" onChange="musicDurationChange(this)" placeholder="00:00:00" pattern="^([0-1][0-9]|[2][0-3]):([0-5][0-9]):([0-5][0-9])$" required disabled>
                        <input type="hidden" class="form-control form-control-sm stopAt" name="stop[1]">
                    </div>
                    <div class="invalid-feedback d-none" id="stop-time-error-1">Stop time must be greater than start time.</div>
                </div>

                <input type="hidden" class="duration" name="duration[1]" value="5">
            </div>
        </div>
    </div>
</div>

<!-- Video during the shuffling -->
<div class="music-setting p-2 mb-1">
    <div class="form-check border-bottom mb-3">
        <input type="checkbox" class="form-check-input toggle-music-settings" name="setting-toggle[2]" id="toggle-music-settings-2" onChange="musicSettingToggleChange(this)">
        <label class="form-check-label" for="toggle-music-settings-2">
            <h6>Video during Brackets Generation</h6>
        </label>
    </div>

    <div class="setting visually-hidden">
        <input type="hidden" name="audioType[<?= MUSIC_TYPE_BRACKET_GENERATION_VIDEO ?>]" value="<?= MUSIC_TYPE_BRACKET_GENERATION_VIDEO ?>">
        <div class="input-group mb-3">
            <div class="input-group-text">
                <input class="form-check-input mt-0" type="radio" value="f" aria-label="Radio button for following text input" name="source[<?= MUSIC_TYPE_BRACKET_GENERATION_VIDEO ?>]" onChange="musicSourceChange(this)" data-target="file" checked>
            </div>
            <input type="file" class="form-control music-source" data-source="file" name="file" onChange="musicFileUpload(this)" accept="video/mp4" required disabled>
            <input type="hidden" class="file-path" id="file-shuffling-video" name="file-path[<?= MUSIC_TYPE_BRACKET_GENERATION_VIDEO ?>]">
            <div class="fileupload-hint form-text">Select a .mp4 file to upload. After waiting a bit, you will notice the player loads the file here when the timestamps appear, in which you could then adjust accordingly.</div>
        </div>
        <div class="input-group mb-3">
            <div class="input-group-text">
                <input class="music-url-enable form-check-input mt-0" type="radio" value="y" aria-label="Radio button for following text input" name="source[<?= MUSIC_TYPE_BRACKET_GENERATION_VIDEO ?>]" onChange="musicSourceChange(this)" data-target="url">
            </div>
            <span class="input-group-text">URL</span>
            <input type="text" class="form-control music-source" data-source="url" aria-describedby="basic-addon3 basic-addon4" name="url[<?= MUSIC_TYPE_BRACKET_GENERATION_VIDEO ?>]" placeholder="Example: https://youtu.be/Gb1iGDchKYs?si=nR-n7KBMHfKWox7U" required disabled>
            <div class="invalid-feedback">This field is required.</div>
            <div class="urlupload-hint form-text d-none">Enter a YouTube video url. <br />Note an <a href="https://developers.google.com/youtube/v3/guides/implementation/videos">API</a> will process the video into a .mp4 file in the backend once you click save. You may specify the timestamps before saving or you may revise the timestamps by navigating to the tournament's music settings action after it's generated.</div>
        </div>
        <div class="mb-3 preview">
            <video controls class="w-100 player">
                <source class="playerSource" src="" type="video/mp4" />
            </video>

            <div class="row g-3">
                <div class="col-6">
                    <div class="input-group">
                        <div class="input-group-text">Start</div>
                        <input type="text" class="form-control time startAt" onChange="musicDurationChange(this)" placeholder="00:00:00" pattern="^([0-1][0-9]|[2][0-3]):([0-5][0-9]):([0-5][0-9])$" required disabled>
                        <input type="hidden" class="form-control form-control-sm startAt" name="start[<?= MUSIC_TYPE_BRACKET_GENERATION_VIDEO ?>]">
                    </div>
                    <div class="invalid-feedback d-none" id="start-time-error-<?= MUSIC_TYPE_BRACKET_GENERATION_VIDEO ?>">Start time must be less than stop time.</div>
                </div>

                <div class="col-6">
                    <div class="input-group">
                        <div class="input-group-text">Stop</div>
                        <input type="text" class="form-control time stopAt" onChange="musicDurationChange(this)" placeholder="00:00:00" pattern="^([0-1][0-9]|[2][0-3]):([0-5][0-9]):([0-5][0-9])$" required disabled>
                        <input type="hidden" class="form-control form-control-sm stopAt" name="stop[<?= MUSIC_TYPE_BRACKET_GENERATION_VIDEO ?>]">
                    </div>
                    <div class="invalid-feedback d-none" id="stop-time-error-<?= MUSIC_TYPE_BRACKET_GENERATION_VIDEO ?>">Stop time must be greater than start time.</div>
                </div>

                <input type="hidden" class="duration" name="duration[<?= MUSIC_TYPE_BRACKET_GENERATION_VIDEO ?>]" value="5">
            </div>
        </div>
    </div>
</div>