<!-- Music during the shuffling -->
<div class="music-setting p-2 mb-1">
    <div class="form-check border-bottom mb-3">
        <input class="form-check-input toggle-music-settings" type="checkbox" name="setting-toggle[0]" id="toggle-music-settings-0">
        <label class="form-check-label" for="toggle-music-settings-0">
            <h6>Music during the generation</h6>
        </label>
    </div>

    <div class="setting visually-hidden">
        <input type="hidden" name="audioType[0]" value="0">
        <div class="input-group mb-3">
            <div class="input-group-text">
                <input class="form-check-input mt-0" type="radio" value="f" aria-label="Radio button for following text input" name="source[0]" data-target="file" checked>
            </div>
            <input type="file" class="form-control music-source" data-source="file" name="file" accept="audio/mpeg,audio/wav,audio/ogg,audio/mid,audio/x-midi">
            <label class="input-group-text" for="file-input">Upload</label>
            <input type="hidden" name="file-path[0]">
        </div>
        <div class="input-group mb-3">
            <div class="input-group-text">
                <input class="form-check-input mt-0" type="radio" value="y" aria-label="Radio button for following text input" name="source[0]" data-target="url">
            </div>
            <span class="input-group-text">URL</span>
            <input type="text" class="form-control music-source" data-source="url" aria-describedby="basic-addon3 basic-addon4" name="url[0]" disabled>
        </div>
        <div class="mb-3 preview">
            <audio controls class="w-100 player">
                <source class="playerSource" src="" type="audio/mpeg" />
            </audio>

            <div class="row row-cols-lg-auto row-cols-md-auto g-3 align-items-center">
                <div class="col-4">
                    <div class="input-group">
                        <div class="input-group-text">Start</div>
                        <input type="text" class="form-control form-control-sm startAt" name="start[0]">
                    </div>
                </div>

                <div class="col-4">
                    <div class="input-group">
                        <div class="input-group-text">Stop</div>
                        <input type="text" class="form-control form-control-sm stopAt" name="stop[0]">
                    </div>

                </div>
                <div class="col-4">
                    <div class="input-group">
                        <div class="input-group-text">Duration</div>
                        <input type="text" class="form-control form-control-sm duration" disabled>
                        <input type="hidden" class="duration" name="duration[0]">
                    </div>
                </div>
            </div>
        </div>
    </div>    
</div>

<!-- Music for the Final Winner -->
<div class="music-setting p-2 mb-1">
    <div class="form-check border-bottom mb-3">
        <input class="form-check-input toggle-music-settings" type="checkbox" name="setting-toggle[1]" id="toggle-music-settings-1">
        <label class="form-check-label" for="toggle-music-settings-1">
            <h6>Music for a Final Winner</h6>
        </label>
    </div>

    <div class="setting visually-hidden">
        <input type="hidden" name="audioType[1]" value="1">
        <div class="input-group mb-3">
            <div class="input-group-text">
                <input class="form-check-input mt-0" type="radio" value="f" aria-label="Radio button for following text input" name="source[1]" data-target="file" checked>
            </div>
            <input type="file" class="form-control music-source" data-source="file" name="file" accept="audio/mpeg,audio/wav,audio/ogg,audio/mid,audio/x-midi">
            <label class="input-group-text" for="file-input">Upload</label>
            <input type="hidden" name="file-path[1]">
            <div class="invalid-feedback">This field is required.</div>
        </div>
        <div class="input-group mb-3">
            <div class="input-group-text">
                <input class="form-check-input mt-0" type="radio" value="y" aria-label="Radio button for following text input" name="source[1]" data-target="url">
            </div>
            <span class="input-group-text">URL</span>
            <input type="text" class="form-control music-source" data-source="url" aria-describedby="basic-addon3 basic-addon4" name="url[1]" disabled>
            <div class="invalid-feedback">This field is required.</div>
        </div>
        <div class="mb-3 preview">
            <audio controls class="w-100 player">
                <source class="playerSource" src="" type="audio/mpeg" />
            </audio>

            <div class="row row-cols-lg-auto row-cols-md-auto g-3 align-items-center">
                <div class="col-4">
                    <div class="input-group">
                        <div class="input-group-text">Start</div>
                        <input type="text" class="form-control form-control-sm startAt" name="start[1]">
                    </div>
                </div>

                <div class="col-4">
                    <div class="input-group">
                        <div class="input-group-text">Stop</div>
                        <input type="text" class="form-control form-control-sm stopAt" name="stop[1]">
                    </div>

                </div>
                <div class="col-4">
                    <div class="input-group">
                        <div class="input-group-text">Duration</div>
                        <input type="text" class="form-control form-control-sm duration" disabled>
                        <input type="hidden" class="duration" name="duration[1]">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>