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
        <div class="visibility-hint form-text">If enabled, the tournament will be visible publicly on the Tournament Gallery. Tournaments listed on the Tournament Gallery may be viewed by spectators/guests as read-only mode, except otherwise allowed through voting.</div>
    </div>
</div>

<div class="border-bottom mb-3 ps-2 pb-3">
    <div class="row g-3 align-items-center">
        <div class="col-auto">
            <label for="evaluationMethod" class="col-form-label">Evaluation Method</label>
        </div>
        <div class="col-auto">
            <select class="form-select" id="evaluationMethod" name="evaluation_method" aria-label="Evaluation Method" onchange="changeEvaluationMethod(this)" required>
                <option value="<?= EVALUATION_METHOD_MANUAL ?>" selected>Manual</option>
                <option value="<?= EVALUATION_METHOD_VOTING  ?>">Voting</option>
            </select>
        </div>
        <div class="evaluation-method-hint form-text ps-3">Determine how tournament bracket participants advance through the rounds.</div>
        <div class="evaluation-method-manual-hint form-text mb-1 ps-3">Tournament host elects the winning bracket participants of each round.</div>
        <div class="evaluation-method-voting-hint form-text mb-1 ps-3 d-none">Winning participants are determined through a consensus. The voting period remains open and concludes once the tournament availability window ends.
            Voting is determined by the action "Vote this participant" available in the tournament.</div>
    </div>

    <div class="voting-settings-panel ps-md-5 ps-sm-3 d-none" id="voting-settings-panel">
        <div class="row g-3 align-items-center">
            <div class="col-auto">
                <label for="votingAccessbility" class="col-form-label">Voting Accessbility</label>
            </div>
            <div class="col-auto">
                <select class="form-select" id="votingAccessbility" name="voting_accessibility" aria-label="voting Accessbility" onchange="changeVotingAccessbility(this)" required>
                    <option value="<?= EVALUATION_VOTING_RESTRICTED ?>" selected>Restricted</option>
                    <option value="<?= EVALUATION_VOTING_UNRESTRICTED  ?>">Unrestricted</option>
                </select>
            </div>
            <div class="evaluation-vote-restricted form-text mb-1 ps-3">
                Only users whom the tournament link is shared with (from Share setting) may vote. Tournament links may be shared publicly for nonregistered (guest) users who prefer to vote anonymously.
                <br />
                Note: All votes by registered and/or anonymous (guest) users are tracked in the "View Log" action available to the host on the Tournament Dashboard. This may be helpful for tracking purposes and monitoring potential spam.
            </div>
            <div class="evaluation-vote-unrestricted form-text mb-1 ps-3 d-none">
                Tournament voting is open for all. Spectators (anonymous guests) may vote as well if tournament visibility on the Gallery is enabled.
                <br />
                Note: All votes by registered and/or anonymous (guest) users are tracked in the "View Log" action available to the host on the Tournament Dashboard. This may be helpful for tracking purposes and monitoring potential spam.
            </div>
        </div>

        <div class="row g-3 align-items-center">
            <div class="col-auto">
                <label for="votingMechanism" class="col-form-label">Voting Mechanism</label>
            </div>
            <div class="col-auto">
                <select class="form-select" id="votingMechanism" name="voting_mechanism" aria-label="Voting Mechanism" onchange="changeVotingMechanism(this)" required>
                    <option value="<?= EVALUATION_VOTING_MECHANISM_ROUND ?>" selected>Round Duration</option>
                    <option value="<?= EVALUATION_VOTING_MECHANISM_MAXVOTE  ?>">Max Votes</option>
                </select>
            </div>
            <div class="evaluation-vote-round form-text mb-1 ps-3">
                Winning participants automatically advance when the duration is reached and the voting period ends each round. Duration is determined by dividing the tournament availability window equally amongst all the rounds.
                <br />
                Note: This option can only be activated if Availability setting is enabled.
            </div>
            <div class="evaluation-vote-max form-text mb-1 ps-3 d-none">
                Winning participants automatically advance when the vote limit is reached each round. Specify the max votes limit per bracket participant below.
            </div>

            <div class="row mb-2 max-vote-setting d-none">
                <div class="col-auto">
                    <label for="maxVotes" class="col-form-label">Max Votes <span class="text-danger">*</span> :</label>
                </div>
                <div class="col-3">
                    <input type="number" name="max_vote_value" id="maxVotes" class="form-control" min="0">
                </div>
            </div>
        </div>
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
                    <div class="input-group" id="startAvPicker" data-td-target-input="nearest" data-td-target-toggle="nearest">
                        <div class="input-group-text">Start</div>
                        <input type="text" name="startAvPicker" class="form-control datetime startAv" id="startAvPickerInput" required>
                        <span class="input-group-text" data-td-target="#startAvPicker" data-td-toggle="datetimepicker">
                            <i class="fas fa-calendar"></i>
                        </span>
                    </div>
                </div>

                <div class="col-6">
                    <div class="input-group" id="endAvPicker" data-td-target-input="nearest" data-td-target-toggle="nearest">
                        <div class="input-group-text">End</div>
                        <input type="text" name="endAvPicker" class="form-control datetime endAv" id="endAvPickerInput" required>
                        <span class="input-group-text" data-td-target="#endAvPicker" data-td-toggle="datetimepicker">
                            <i class="fas fa-calendar"></i>
                        </span>
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
                <input type="number" name="score_bracket" id="scorePerBracket" class="form-control" min="0" required>
            </div>
        </div>
        <div class="row">
            <div class="col-6 form-check ps-2">
                <input type="checkbox" id="enableIncrementScore" class="form-check-input ms-0" name="increment_score_enabled" onChange="toggleIncrementScore(this)" checked>
                <label for="enableIncrementScore" class="form-check-label ms-1">Increment Score :</label>
            </div>
            <div class="col-3 ms-1">
                <input type="number" name="increment_score" id="incrementScore" class="form-control" min="0" required>
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