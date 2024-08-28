<div class="input-group mb-3">
    <span class="input-group-text" id="type">Elimination Type</span>
    <select class="form-select" id="eliminationType" name="type" aria-label="type" onchange="changeEliminationType(this)" required>
        <option value="1" selected>Single</option>
        <option value="2">Double</option>
    </select>
    <div class="single-type-hint form-text">During a Single Elimination tournament, a single loss means that the competitor is eliminated and has no more matches to play. The tournament will naturally conclude with a Grand Final between the two remaining undefeated participants.</div>
    <div class="double-type-hint form-text d-none">A Double Elimination tournament allows each competitor to be eliminated twice. The tournament is generated with the brackets duplicated.</div>
    <div class="elimination-type-hint form-text mt-2">
        Note that after updating the elimination type of the tournament, the dashboard will reflect this change once you click save and refresh, however the brackets will not actually change unless you execute "reset" action, select the tournament from the dashboard, and regenerate the brackets with the new settings.
    </div>
</div>

<div class="input-group mb-3">
    <textarea id="description" name="description"></textarea>
    <div class="form-text">Enter an optional description that will be displayed in the tournament.</div>
</div>

<div class="form-check border-bottom mb-3 pb-3">
    <div class="ps-2">
        <input type="checkbox" class="form-check-input enable-visibility" name="visibility" id="enableVisibility" onChange="toggleVisibility(this)" checked>
        <label class="form-check-label" for="enableVisibility">
            <h6>Visibility</h6>
        </label>
        <div class="visibility-hint form-text">If enabled, the tournament will be visible publicly on the Tournament Gallery. Tournaments listed on the Tournament Gallery may be viewed by spectators/guests as read-only mode.</div>
    </div>
</div>

<div class="form-check border-bottom mb-3 pb-3">
    <div class="ps-2">
        <input type="checkbox" class="form-check-input enable-availability" name="availability" id="enableAvailability" onChange="toggleAvailability(this)" checked>
        <label class="form-check-label" for="enableAvailability">
            <h6>Availability</h6>
        </label>
        <div class="availability-option">
            <div class="availability-hint form-text">Specify tournament availability window.</div>
            
            <div class="row mt-3">
                <div class="col-6">
                    <div class="input-group">
                        <div class="input-group-text">Start</div>
                        <input type="text" class="form-control datetime startAv" id="startAvPicker"  readonly>
                        <input type="hidden" class="form-control form-control-sm startAt" name="start[0]">
                    </div>
                </div>

                <div class="col-6">
                    <div class="input-group">
                        <div class="input-group-text">End</div>
                        <input type="text" class="form-control datetime endAv" id="endAvPicker" required readonly>
                        <input type="hidden" class="form-control form-control-sm stopAt" name="stop[0]">
                    </div>
                    <div class="invalid-feedback d-none" id="stop-time-error-0">Stop time must be greater than start time.</div>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="form-check mb-3">
    <div class="ps-2">
        <input type="checkbox" class="form-check-input" name="score_enabled" id="enableScoreOption" onChange="toggleScoreOption(this)" checked>
        <label class="form-check-label" for="enableScoreOption">
            <h6>Enable Scoring</h6>
        </label>
        <div class="enable-scoreoption-hint form-text">If enabled, a score associated with each bracket will be accumulated towards a final score. You may specify the points a participant could gain below.</div>
    </div>
    <div class="ps-2" id="scoreOptions">
        <div class="row mb-2">
            <div class="col-auto">
                <label for="scorePerBracket" class="col-form-label">Score per bracket per round <span class="text-danger">*</span> :</label>
            </div>
            <div class="col-3">
                <input type="number" name="score_bracket" id="scorePerBracket" class="form-control" min="0" value="0" required>
            </div>
        </div>
        <div class="row">
            <div class="col-6 form-check ps-2">
                <input type="checkbox" id="enableIncrementScore" class="form-check-input ms-0" name="increment_score_enabled" onChange="toggleIncrementScore(this)" checked>
                <label for="enableIncrementScore" class="form-check-label ms-1">Increment Score :</label>
            </div>
            <div class="col-3 ms-1">
                <input type="number" name="increment_score" id="incrementScore" class="form-control" min="0" value="0" required>
            </div>
        </div>
        <div class="enable-increamentscoreoption-hint form-text">
            <p>Specify an increment the score should increase by for each round.</p>
            <p>
                For example, if winning participants attain 100 points in their bracket in round 1, and an increment of 200 is specified, then in round 2, winning participants will attain 300 points, and in round 3 winning participants will attain 500 points, etc.
                In this case, the cumulative result would be accumulated each round as follows:
                100 + 300 + ...
            </p>
        </div>
    </div>
</div>

<div class="form-check mb-3">
    <div class="ps-2">
        <input type="checkbox" class="form-check-input enable-shuffling" name="shuffle_enabled" id="enableShuffle" onChange="toggleShuffleParticipants(this)" checked>
        <label class="form-check-label" for="enableShuffle">
            <h6>Shuffle Participants</h6>
        </label>
        <div class="enable-shuffling-hint form-text">If enabled, the contestant brackets will be generated with the participants shuffled.</div>
        <div class="disable-shuffling-hint form-text d-none">If disabled, the participants will not be shuffled and the contestant brackets will be generated in the same order displayed in the participants list.</div>
    </div>
</div>