<div class="input-group mb-3">
    <span class="input-group-text" id="tournamentTitleLabel">Tournament Title</span>
    <input type="text" class="form-control music-source" id="tournamentTitle" name="title" required>
    <div class="invalid-feedback">This field is required.</div>
</div>

<div class="input-group mb-3">
    <span class="input-group-text" id="type">Elimination Type</span>
    <select class="form-select" id="eliminationType" name="type" aria-label="type" onchange="changeEliminationType(this)" required>
        <option value="<?= TOURNAMENT_TYPE_SINGLE ?>" selected>Single</option>
        <option value="<?= TOURNAMENT_TYPE_DOUBLE ?>">Double</option>
        <option value="<?= TOURNAMENT_TYPE_KNOCKOUT ?>">Knockout</option>
        <option value="<?= TOURNAMENT_TYPE_FFA ?>">Free For All (FFA) </option>
        <option value="<?= TOURNEMENT_TYPE_RROBIN ?>">Round Robin</option>
        <option value="<?= TOURNEMENT_TYPE_SWISS ?>">Swiss</option>
    </select>
    <div class="read-more-container">
        <div class="single-type-hint form-text text-content">
            <?= lang('Descriptions.tournamentSingleTypeDesc') ?>
            <div class="elimination-type-update-note mt-2"></div>
        </div>
        <div class="double-type-hint form-text d-none">
            <?= lang('Descriptions.tournamentDoubleTypeDesc') ?>
            <div class="elimination-type-update-note mt-2"></div>
        </div>
        <div class="knockout-type-hint form-text d-none">
            <?= lang('Descriptions.tournamentKockoutTypeDesc') ?>
            <div class="elimination-type-update-note mt-2"></div>
        </div>
        <div class="elimination-type-hint d-none">
            Note that after updating the elimination type of the tournament, the dashboard will reflect this change once you click save and refresh, however the brackets will not actually change unless you execute "reset" action, select the tournament from the dashboard, and regenerate the brackets with the new settings.
        </div>
    </div>
</div>

<div class="row g-3 ms-2 mb-5 d-none" id="FFA_Option">
    <div class="d-flex align-items-center">
        <div class="col-4">
            <label for="match_group_size" class="col-form-label">Max of group members <span class="text-danger">*</span></label>
        </div>
        <div class="col-4">
            <input type="number" id="max_group_size" name="max_group_size" class="form-control" aria-describedby="matchGroupSizeHelpInline" required disabled>
        </div>
    </div>
    <div class="d-flex align-items-center m-0">
        <div class="col-12 ps-3 read-more-container">
            <span id="matchGroupSizeHelpInline" class="form-text text-content">
                <?= lang('Descriptions.tournamentFFAMaxGroupSizeDesc') ?>
            </span>
        </div>
    </div>
    <div class="d-flex align-items-center">
        <div class="col-4">
            <label for="advance_count" class="col-form-label">Advancing Count <span class="text-danger">*</span></label>
        </div>
        <div class="col-4">
            <input type="number" id="advance_count" name="advance_count" class="form-control" aria-describedby="matchGroupSizeHelpInline" required disabled>
        </div>
    </div>
    <div class="d-flex align-items-center m-0">
        <div class="col-12 ps-3 read-more-container">
            <span id="matchGroupSizeHelpInline" class="form-text">
                <?= lang('Descriptions.tournamentFFAAdvancingSizeDesc') ?>
            </span>
        </div>
    </div>
</div>
<div class="row g-3 ms-2 mb-5 align-items-center d-none" id="FFA_Option">

</div>

<div class="input-group mb-3">
    <span class="input-group-text" id="theme">Theme</span>
    <select class="form-select" id="tournamentTheme" name="theme" aria-label="Theme" onchange="changeTournamentTheme(this)" required>
        <option value="<?= TOURNAMENT_THEME_CLASSIC ?>" selected>Classic</option>
        <option value="<?= TOURNAMENT_THEME_CHAIMPIONSHIP ?>">Championship Gold</option>
        <option value="<?= TOURNAMENT_THEME_DARKROYALE ?>">Dark Royale</option>
        <option value="<?= TOURNAMENT_THEME_GRIDIRON ?>">Gridiron</option>
        <option value="<?= TOURNAMENT_THEME_MODERNMETAL ?>">Modern Metal</option>
    </select>
    <div class="tournament-theme-settings-hints ms-2">
        <div class="theme-classic-hint form-text ps-md-3"><?= lang('Descriptions.tournamentThemeClassicDesc') ?></div>
        <div class="theme-champion-hint form-text mb-1 ps-md-3 d-none"><?= lang('Descriptions.tournamentThemeChampionDesc') ?></div>
        <div class="theme-darkroyale-hint form-text mb-1 ps-md-3 d-none"><?= lang('Descriptions.tournamentThemeDarkroyaleDesc') ?></div>
        <div class="theme-gridiron-hint form-text mb-1 ps-md-3 d-none"><?= lang('Descriptions.tournamentThemeGridironDesc') ?></div>
        <div class="theme-modernmetal-hint form-text mb-1 ps-md-3 d-none"><?= lang('Descriptions.tournamentThemeModernmetaleDesc') ?></div>
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
        <div class="visibility-hint form-text"><?= lang('Descriptions.tournamentVisibilityDesc') ?></div>
    </div>
</div>

<div class="form-check border-bottom mb-3 pb-3">
    <div class="ps-2">
        <input type="checkbox" class="form-check-input enable-availability" name="availability" id="enableAvailability" onChange="toggleAvailability(this)" checked>
        <label class="form-check-label" for="enableAvailability">
            <h6>Availability</h6>
        </label>
        <div class="availability-option">
            <div class="availability-hint form-text"><?= lang('Descriptions.tournamentAvailabilityDesc') ?></div>

            <div class="row mt-3">
                <div class="col-6">
                    <div class="input-group" id="startAvPicker" data-td-target-input="nearest" data-td-target-toggle="nearest">
                        <div class="input-group-text">Start</div>
                        <input type="text" name="startAvPicker" class="form-control datetime startAv" id="startAvPickerInput" pattern="^(19[0-9]{2}|20[0-9]{2})-\d{2}-\d{2} \d{2}:\d{2}$" readonly required>
                        <span class="input-group-text" data-td-target="#startAvPicker" data-td-toggle="datetimepicker">
                            <i class="fas fa-calendar"></i>
                        </span>
                    </div>
                    <div class="invalid-feedback d-none" id="availability-start-date-error"></div>
                </div>

                <div class="col-6">
                    <div class="input-group" id="endAvPicker" data-td-target-input="nearest" data-td-target-toggle="nearest">
                        <div class="input-group-text">End</div>
                        <input type="text" name="endAvPicker" class="form-control datetime endAv" id="endAvPickerInput" pattern="^(19[0-9]{2}|20[0-9]{2})-\d{2}-\d{2} \d{2}:\d{2}$" readonly required>
                        <span class="input-group-text" data-td-target="#endAvPicker" data-td-toggle="datetimepicker">
                            <i class="fas fa-calendar"></i>
                        </span>
                    </div>
                    <div class="invalid-feedback d-none" id="availability-end-date-error">Stop date must be equal or greater than current date.</div>
                </div>

            </div>
        </div>
    </div>
</div>

<div class="evaluation-settings border-bottom mb-3 ps-2 pb-3">
    <div class="row g-3 align-items-center">
        <div class="col-auto">
            <label for="evaluationMethod" class="col-form-label"><strong>Evaluation Method</strong></label>
        </div>
        <div class="col-auto">
            <select class="form-select" id="evaluationMethod" name="evaluation_method" aria-label="Evaluation Method" onchange="changeEvaluationMethod(this)" required>
                <option value="<?= EVALUATION_METHOD_MANUAL ?>" selected>Manual</option>
                <option value="<?= EVALUATION_METHOD_VOTING ?>">Voting</option>
            </select>
        </div>
        <div class="read-more-container">
            <div class="evaluation-method-hint form-text ps-3">Determines how tournament bracket participants advance through the rounds.</div>
            <div class="evaluation-method-manual-hint form-text text-content mb-1 ps-3"><?= lang('Descriptions.tournamentEvaluationManualDesc') ?></div>
            <div class="evaluation-method-voting-hint form-text mb-1 ps-3 d-none"><?= lang('Descriptions.tournamentEvaluationVotingDesc') ?></div>
        </div>
    </div>
    <div class="clearfix"></div>
    <div class="voting-settings-panel ps-md-3 ps-sm-2 d-none" id="voting-settings-panel">
        <div class="row g-2 align-items-center">
            <div class="col-auto">
                <label for="votingAccessbility" class="col-form-label"><strong>Voting Accessibility</strong></label>
            </div>
            <div class="col-auto">
                <select class="form-select" id="votingAccessbility" name="voting_accessibility" aria-label="voting Accessibility" onchange="changeVotingAccessbility(this)" required>
                    <option value="<?= EVALUATION_VOTING_RESTRICTED ?>" selected>Restricted</option>
                    <option value="<?= EVALUATION_VOTING_UNRESTRICTED ?>">Unrestricted</option>
                </select>
            </div>
            <div class="read-more-container">
                <div class="evaluation-vote-restricted form-text text-content mb-1 ps-3"><?= lang('Descriptions.tournamentVotingRestrictedgDesc') ?></div>
                <div class="evaluation-vote-unrestricted form-text mb-1 ps-3 d-none"><?= lang('Descriptions.tournamentVotingUnrestrictedDesc') ?></div>
            </div>
        </div>

        <div class="row g-2 align-items-center">
            <div class="col-auto">
                <label for="votingMechanism" class="col-form-label"><strong>Voting Mechanism</strong></label>
            </div>
            <div class="col-auto">
                <select class="form-select" id="votingMechanism" name="voting_mechanism" aria-label="Voting Mechanism" onchange="changeVotingMechanism(this)" required>
                    <option value="<?= EVALUATION_VOTING_MECHANISM_ROUND ?>" selected>Round Duration</option>
                    <option value="<?= EVALUATION_VOTING_MECHANISM_MAXVOTE ?>">Max Votes</option>
                    <option value="<?= EVALUATION_VOTING_MECHANISM_OPENEND ?>">Open-Ended</option>
                </select>
            </div>
            <div class="read-more-container">
                <div class="evaluation-vote-round-availability-required form-text mb-1 ps-3 d-none">
                    * Availability must be enabled to use this setting!
                </div>
                <div class="evaluation-vote-round form-text text-content mb-1 ps-3"><?= lang('Descriptions.tournamentVotingRoundDurationDesc') ?></div>
                <div class="evaluation-vote-max form-text mb-1 ps-3 d-none"><?= lang('Descriptions.tournamentVotingMaxVotesDesc') ?></div>
                <div class="evaluation-open-ended form-text mb-1 ps-3 d-none"><?= lang('Descriptions.tournamentVotingOpenEndedDesc') ?></div>

                <div class="row mb-2 max-vote-setting d-none">
                    <div class="col-auto">
                        <label for="maxVotes" class="col-form-label">Max Votes <span class="text-danger">*</span> :</label>
                    </div>
                    <div class="col-3">
                        <input type="number" name="max_vote_value" id="maxVotes" class="form-control" min="0">
                    </div>
                    <div class="evaluation-vote-max-limit form-text mb-1 ps-3"><?= lang('Descriptions.tournamentVotingMaxVoteLimitDesc') ?></div>
                </div>
            </div>
        </div>

        <div class="mt-2">
            <input type="checkbox" class="form-check-input" name="voting_retain" id="retainVotesCheckbox">
            <label class="form-check-label" for="retainVotesCheckbox">Retain vote count across rounds</label>
            <div class="read-more-container">
                <div class="retain-votes-checkbox-hint form-text text-content ps-3"><?= lang('Descriptions.tournamentRetainVoteCountDesc') ?></div>
            </div>
        </div>
        <div class="mt-2 allow-host-override-setting">
            <input type="checkbox" class="form-check-input" name="allow_host_override" id="allowHostOverride">
            <label class="form-check-label" for="allowHostOverride">Allow Host override</label>
            <div class="retain-votes-checkbox-hint form-text ps-3"><?= lang('Descriptions.tournamentAllowHostOverrideDesc') ?></div>
        </div>
    </div>
    <div class="round-duration-combine ps-md-3 ps-sm-2">
        <div class="mt-2">
            <input type="checkbox" class="form-check-input" name="round_duration_combine" id="roundDurationCheckbox" onchange="toggleRoundDuration(this)">
            <label class="form-check-label" for="roundDurationCheckbox">Round Duration</label>
            <div class="round-duration-combine-required text-danger form-text mb-1 ps-3 d-none">
                * Availability must be enabled to use this setting!
            </div>
            <div class="read-more-container">
                <div class="round-duration-maxVote-checkbox-hint form-text ps-3 d-none"><?= lang('Descriptions.tournamentVotingRoundDurationDesc') ?></div>
                <div class="round-duration-manual-checkbox-hint form-text text-content ps-3"><?= lang('Descriptions.tournamentManualRoundDurationDesc') ?></div>
            </div>
        </div>
    </div>
    <div class="round-duration-settings ps-md-3 ps-sm-2 d-none">
        <div class="ps-3">
            <div class="form-check">
                <input class="form-check-input" type="radio" name="timer_option" value="auto" id="timerOptionAuto" onchange="changeRoundMode(this)" checked>
                <label class="form-check-label" for="timerOptionAuto">
                    Auto-calculate from Tournament Availability
                </label>
                <br />
                <span class="form-text"><?= lang('Descriptions.roundAutoCalculateDescription') ?></span>
            </div>
            <div class="form-check">
                <input class="form-check-input" type="radio" name="timer_option" value="custom" id="timerOptionCustom" onchange="changeRoundMode(this)">
                <label class="form-check-label" for="timerOptionCustom">
                    Custom per Round
                </label>
                <br />
                <span class="form-text"><?= lang('Descriptions.manualRoundDurationDescription') ?></span>

                <div class="custom-timer d-none">
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="round_time_type" value="<?= TOURNAMENT_CUSTOM_TIMER_SAME ?>" id="customTimerSame" onchange="changeRoundTimeMode(this)" checked>
                        <label class="form-check-label" for="customTimerSame">Same for All Rounds</label>
                    </div>
                    <div class="form-check form-check-inline">
                        <input class="form-check-input" type="radio" name="round_time_type" value="<?= TOURNAMENT_CUSTOM_TIMER_PER_ROUNDS ?>" id="customTimerPerRounds" onchange="changeRoundTimeMode(this)" disabled>
                        <label class="form-check-label" for="customTimerPerRounds">Per rounds</label>
                    </div>

                    <div class="round-time-wrapper mt-1 mb-1 d-flex" id="roundTimeWrapper"></div>
                </div>
            </div>

            <div class="custom-timer-options mt-3" id="customTimerOptions">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="timer_auto_advance" id="timerAutoAdvance" checked>
                    <label class="form-check-label" for="timerAutoAdvance">
                        Auto-Advance when timer ends
                    </label>
                    <br />
                    <span class="form-text"><?= lang('Descriptions.autoAdvanceByTimerEndDescription') ?></span>
                </div>
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" name="timer_require_scores" id="timerRequireScores" checked>
                    <label class="form-check-label" for="timerRequireScores">
                        Require scores before deadline
                    </label>
                    <br />
                    <span class="form-text"><?= lang('Descriptions.requireScoresBeforeDeadlineDescription') ?></span>
                </div>
            </div>
        </div>

        <div class="collapse-toggle" role="button" data-bs-toggle="collapse" href="#collapseAdvancedRoundSettings" aria-expanded="false" aria-controls="collapseExample">
            Advanced Timer Options
        </div>
        <div class="collapse" id="collapseAdvancedRoundSettings">
            <div class="ps-3">
                <div class="ps-md-3 ps-sm-4 pt-2" id="manualRoundSettings">
                    <label class="form-label pt-2">⏱️ Timer Start</label>
                    <div class="timer-start-options ps-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="timer_start_option" value="<?= AUTOMATIC ?>" id="timerAutoStart" checked>
                            <label class="form-check-label" for="timerAutoStart">
                                Start automatically when round begins
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="timer_start_option" value="<?= MANUAL ?>" id="timerManualStart">
                            <label class="form-check-label" for="timerManualStart">
                                Start manually
                            </label>
                        </div>
                    </div>

                    <label class="form-label pt-2">🔒 Lock Scores at Deadline</label>
                    <div class="timer-start-options ps-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="round_score_editing" value="0" id="preventScoreEditing" checked>
                            <label class="form-check-label" for="preventScoreEditing">
                                Prevent score editing after timer ends
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="round_score_editing" value="1" id="allowScoreEditing">
                            <label class="form-check-label" for="allowScoreEditing">
                                Allow editing even after timer ends
                            </label>
                        </div>
                    </div>

                    <label class="form-label pt-2">🔄 Auto-Advance at Deadline</label>
                    <div class="timer-start-options ps-3">
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="round_advance_method" value="<?= AUTOMATIC ?>" id="roundAutoAdvance" checked>
                            <label class="form-check-label" for="roundAutoAdvance">
                                Automatically determine advancing participants when timer ends
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="round_advance_method" value="<?= MANUAL ?>" id="roundManualAdvance">
                            <label class="form-check-label" for="roundManualAdvance">
                                Require host approval before advancing
                            </label>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="scoring-settings border-bottom form-check mb-3 pb-3">
    <div class="ps-2">
        <input type="checkbox" class="form-check-input" name="score_enabled" id="enableScoreOption" onChange="toggleScoreOption(this)" checked>
        <label class="form-check-label" for="enableScoreOption">
            <h6>Enable Scoring</h6>
        </label>
        <div class="enable-scoreoption-hint form-text"><?= lang('Descriptions.tournamentEnableScoringDesc') ?></div>
    </div>
    <div class="ps-2">
        <div class="input-group mb-3">
            <span class="input-group-text" id="type">Scoring Method</span>
            <select class="form-select" id="scoringMethod" name="scoring_method" aria-label="type" onchange="changeScoringMethod(this)" required>
                <option value="<?= TOURNAMENT_SCORE_SYSTEM_DEFINED ?>" data-option="system">System-Defined Scoring</option>
                <option value="<?= TOURNAMENT_SCORE_MANUAL_ENTRY ?>" data-option="manual" selected>Manual Score Entry</option>
            </select>
        </div>
    </div>
    <div class="ps-2" id="scoreOptions" class="d-none">
        <div class="row mb-2">
            <div class="col-auto">
                <label for="scorePerBracket" class="col-form-label">Score per bracket per round <span class="text-danger">*</span> :</label>
            </div>
            <div class="col-3">
                <input type="number" name="score_bracket" id="scorePerBracket" class="form-control" min="0" required>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 col-sm-6 form-check ps-2">
                <input type="checkbox" id="enableIncrementScore" class="form-check-input ms-0" name="increment_score_enabled" onChange="toggleIncrementScore(this)" checked>
                <label for="enableIncrementScore" class="form-check-label ms-1">Increment Score :</label>
            </div>
            <div class="col-3 ms-1">
                <input type="number" name="increment_score" id="incrementScore" class="form-control" min="0" step=".01" required>
            </div>
            <div class="col-auto">
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="increment_score_type" id="incrementPlus" value="<?= TOURNAMENT_SCORE_INCREMENT_PLUS ?>" onchange="changeIncrementScoreType(this)" checked>
                    <label class="form-check-label" for="incrementPlus">Plus</label>
                </div>
                <div class="form-check form-check-inline">
                    <input class="form-check-input" type="radio" name="increment_score_type" id="incrementMultiply" value="<?= TOURNAMENT_SCORE_INCREMENT_MULTIPLY ?>" onchange="changeIncrementScoreType(this)">
                    <label class="form-check-label" for="incrementMultiply">Multiply</label>
                </div>
                <div class="form-check form-check-inline">
                    <label class="col-form-label">&nbsp;</label>
                </div>
            </div>
        </div>
        <div class="enable-increamentscoreoption-hint form-text ps-3">
            <div class="read-more-container">
                <p><?= lang('Descriptions.tournamentIncrementScoreDesc') ?></p>
                <div class="plus text-content"><?= lang('Descriptions.tournamentIncrementScoreTypePlusDesc') ?></div>
                <div class="multiply d-none"><?= lang('Descriptions.tournamentIncrementScoreTypeMultipleDesc') ?></div>
            </div>
        </div>
        <div class="ps-3">
            <input type="checkbox" class="form-check-input" name="score_manual_override" id="enableScoreOverride" checked>
            <label class="form-check-label" for="enableScoreOverride">
                <h6>Enable Manual Override</h6>
            </label>
            <div class="enable-scoreoverride-hint form-text"><?= lang('Descriptions.tournamentEnableScoreOverrideDesc') ?></div>
        </div>
    </div>
</div>

<div class="manage-metrics border-bottom mb-3">
    <div class="form-check mb-3">
        <div class="ps-2">
            <input type="checkbox" class="form-check-input" name="participant_manage_metrics" id="allowParticipantManageMetrics" checked>
            <label class="form-check-label" for="allowParticipantMatchMetrics">
                <h6>Allow Participants to Manage Match Metrics</h6>
            </label>
            <div class="allow-participant-match-metrics-hint form-text"><?= lang('Descriptions.allowParticipantMatchMetrics') ?></div>
        </div>
    </div>

    <div class="form-check mb-3">
        <div class="ps-2">
            <input type="checkbox" class="form-check-input" name="host_manage_metrics" id="allowHostManageMetrics" checked>
            <label class="form-check-label" for="allowHostMatchMetrics">
                <h6>Allow Organizer/Host to Manage Match Metrics</h6>
            </label>
            <div class="allow-host-match-metrics-hint form-text"><?= lang('Descriptions.allowHostMatchMetrics') ?></div>
        </div>
    </div>

    <div class="form-check mb-3">
        <div class="ps-2">
            <input type="checkbox" class="form-check-input" name="allow_metric_edits" id="allowMetricEdits" checked>
            <label class="form-check-label" for="allowMetricEdits">
                <h6>Allow Metric Edits After Submission</h6>
            </label>
            <div class="allow-metric-edits-hint form-text"><?= lang('Descriptions.allowMetricEdits') ?></div>
        </div>
    </div>
</div>

<div class="form-check mb-3">
    <div class="ps-2">
        <input type="checkbox" class="form-check-input enable-shuffling" name="shuffle_enabled" id="enableShuffle" onChange="toggleShuffleParticipants(this)" checked>
        <label class="form-check-label" for="enableShuffle">
            <h6>Shuffle Participants</h6>
        </label>
        <div class="enable-shuffling-hint form-text"><?= lang('Descriptions.tournamentShuffleParticipantsEnabledDesc') ?></div>
        <div class="disable-shuffling-hint form-text d-none"><?= lang('Descriptions.tournamentShuffleParticipantsDisabledDesc') ?></div>
    </div>
</div>

<div class="form-check mb-3">
    <div class="ps-2">
        <input type="checkbox" class="form-check-input enable-shuffling" name="pt_image_update_enabled" id="ptImageUpdatePermission">
        <label class="form-check-label" for="ptImageUpdatePermission">
            <h6>Participant Image Customization Access</h6>
        </label>
        <div class="read-more-container">
            <div class="form-text text-content"><?= lang('Descriptions.tournamentParticipantImageCustomizationDesc') ?></div>
        </div>
    </div>
</div>