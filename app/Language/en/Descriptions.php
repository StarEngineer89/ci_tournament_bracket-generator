<?php

return [
    'tournamentSingleTypeDesc' => 'During a Single Elimination tournament, a single loss means that the competitor is eliminated and has no more matches to play. The tournament will naturally conclude with a Grand Final between the two remaining undefeated participants.',
    'tournamentDoubleTypeDesc' => 'A Double Elimination tournament allows each competitor to be eliminated twice. The tournament is generated with the brackets duplicated.',
    'tournamentKockoutTypeDesc' => 'A Knockout Bracket/Stage is a typical single-elimination bracket. It showcases a mirrored tree of participants converging to the final stage, in the center, in which the winner is determined.',
    'tournamentFFAMaxGroupSizeDesc' => "
            Max number of participants per match (group). The system will automatically divide participants into groups based on this value.<br />
            In case the number of participants doesn’t divide evenly, the system will try to adjust group sizes for fairness. <br />
            For example, if you set the max group size to 5 and have 11 participants, the group size may be reduced to 4 to avoid a final group with just 1 participant.
            The goal is to ensure no group is unfairly small or unbalanced, while staying close to your configured limit",
    'tournamentFFAAdvancingSizeDesc' => "
            Number of participants who advance from each match (group).<br />
            This setting controls how many top performers from each group will move on to the next round.<br />
            For example, if 3 participants advance per group and you have 3 groups, then 9 participants total will advance.",
    'tournamentThemeClassicDesc' => 'A clean and simple bracket design, perfect for any tournament.',
    'tournamentThemeChampionDesc' => 'Bring a touch of luxury and prestige to your tournament.',
    'tournamentThemeDarkroyaleDesc' => 'A sleek and intense theme for high-stakes competition.',
    'tournamentThemeGridironDesc' => 'Inspired by the football field, ideal for sports-themed tournaments.',
    'tournamentThemeModernmetaleDesc' => 'A bold and industrial look, adding strength to your tournament bracket.',
    'tournamentVisibilityDesc' => 'If enabled, the tournament will be visible publicly on the Tournament Gallery. Tournaments listed on the Tournament Gallery may be viewed by spectators/guests as read-only mode, except otherwise allowed through voting.',
    'tournamentAvailabilityDesc' => 'The tournament availability window.',
    'tournamentEvaluationManualDesc' => 'Tournament host elects the winning bracket participants of each round.',
    'tournamentEvaluationVotingDesc' => 'Winning participants are determined through a consensus. The voting period remains open and concludes once the tournament availability window ends.<br/>Voting is determined by the action (+) available on each participant in the tournament.',
    'tournamentVotingRestrictedgDesc' => 'Only users whom the tournament link is shared with (from Share setting) may vote. Tournament links may be shared publicly for nonregistered (guest) users who prefer to vote anonymously. Enabling visibility setting (i.e. making tournament accessible on the gallery) overrides this restriction option.<br />Note: All votes by registered and/or anonymous (guest) users are tracked in the "View Log" action available to the host on the Tournament Dashboard. This may be helpful for tracking purposes and monitoring potential spam.',
    'tournamentVotingUnrestrictedDesc' => 'Tournament voting is open for all. Spectators (anonymous guests) may vote as well if tournament visibility on the Gallery is enabled.<br />Note: All votes by registered and/or anonymous (guest) users are tracked in the "View Log" action available to the host on the Tournament Dashboard. This may be helpful for tracking purposes and monitoring potential spam.',
    'tournamentVotingRoundDurationDesc' => 'Winning participants automatically advance when the duration is reached and the voting period ends each round. Duration is determined by dividing the tournament availability window equally amongst all the rounds. In case of a tie in votes (for example, suppose both participants in the same bracket in the same round attain 100 votes) then the system will automatically mark a participant amongst the two as winner of the bracket randomly.<br />Note: This option can only be activated if Availability setting is enabled.',
    'tournamentManualRoundDurationDesc' => 'Participants automatically advance (randomly) when the duration ends for the round. Duration is determined by dividing the tournament availability window equally amongst all the rounds.<br/>Note: This option can only be activated if Availability setting is enabled.',
    'tournamentVotingMaxVotesDesc' => 'Winning participants automatically advance when the vote limit is reached each round.',
    'tournamentVotingMaxVoteLimitDesc' => 'The max votes limit per bracket participant per round',
    'tournamentVotingOpenEndedDesc' => "Winning participants advance through a combination of votes and manual intervention by tournament host to mark the winner. This is ideal if the tournament availability duration is not set/unknown. For example, if participant1 attains 100 votes and participant2 attains 30 votes in bracket 1 in round 1 but the tournament is slowly advancing, then the host could determine that's enough votes to mark the winners for the next stage.",
    'tournamentRetainVoteCountDesc' => "By default, the vote count will reset for each round. <br/>By enabling this option, the vote count is preserved and will instead accumulate each round. <br/>Note: For double tournaments, the vote is retained cumulatively from both brackets in a round the participant is in. For example, if participant1 was in bracket1 and bracket 3 in a double tournament, and participant1 gained 3 votes in bracket1 and 5 votes in bracket2 and advanced to the next round, the vote will be aggregated as 3 + 5 = 8 in next round.",
    'tournamentAllowHostOverrideDesc' => "If this setting is enabled, the tournament host can intervene and manually mark the winners of each bracket in each round, regardless of the vote count.",
    'tournamentRoundDurationCombineManual' => "Participants automatically advance (randomly) when the duration ends for the round. Duration is determined by dividing the tournament availability window equally amongst all the rounds. Note: This option can only be activated if Availability setting is enabled.",
    'tournamentRoundDurationCombineMaxVote' => "Winning participants automatically advance when the duration is reached and the voting period ends each round. Duration is determined by dividing the tournament availability window equally amongst all the rounds. In case of a tie in votes (for example, suppose both participants in the same bracket in the same round attain 100 votes) then the system will automatically mark a participant amongst the two as winner of the bracket randomly. Note: This option can only be activated if Availability setting is enabled.",
    'tournamentEnableScoringDesc' => "If enabled, a score associated with each bracket will be accumulated towards a final score.",
    'tournamentEnableScoreOverrideDesc' => "Allow host to manually edit scores even when system-defined scoring is enabled.",
    'tournamentShuffleParticipantsEnabledDesc' => "If enabled, the contestant brackets will be generated with the participants shuffled.",
    'tournamentShuffleParticipantsDisabledDesc' => "If disabled, the participants will not be shuffled and the contestant brackets will be generated in the same order displayed in the participants list.",
    'tournamentParticipantImageCustomizationDesc' => "
            Enabling this setting allows all users, including guests/visitors with access to the tournament, to update participant images without requiring general edit permissions on the tournament link.<br />
            This feature provides flexibility in customizing participant images while maintaining control over other aspects of the tournament bracket.<br />
            Note: Edit permissions on a shared url override this setting; so in other words, if this setting is disabled but the tournament host shared a link with users that is configured with edit permissions on the tournament brackets, they will be able to update the participant images regardless of this setting.",
    'tournamentPlayForEveryoneDesc' => "If enabled, the audio will play once final winner is selected for everyone, meaning any device that has the tournament opened at the time the final winner is determined, will play the audio synchronously as well.",
    'tournamentIncrementScoreDesc' => "An increment the score should increase by for each round.",
    'tournamentIncrementScoreTypePlusDesc' => "For example, if winning participants attain 100 points in their bracket in round 1, and an increment of 200 is specified, then in round 2, winning participants will attain 300 points, and in round 3 winning participants will attain 500 points, etc.
                In this case, the cumulative result would be accumulated each round as follows:
                100 + 300 + ...",
    'tournamentIncrementScoreTypeMultipleDesc' => "For example, if winning participants attain 100 points in their bracket in round 1, and a multiplier of 1.5 is specified, then in round 2, winning participants will attain 150 points (100 * 1.5 => 150), and in round 3 winning participants will attain 225 points (150 * 1.5 => 225), etc.
                In this case, the cumulative result would be accumulated each round as follows: 100 (round1) + 250 (round1 + round2) + 475 (round1 + round2 + round3) + ...",
    'tournamentAudioFinalWinnerDesc' => "If enabled, an audio plays upon the Final Winner's selection.",
    'allowParticipantMatchMetrics' => "
        Registered participants can manage their own match metrics (e.g., scores, timers, time submissions), or, if part of a group, the metrics for their team.<br/>
        All actions are logged for transparency and audit purposes.
    ",
    'allowHostMatchMetrics' => "
        The tournament organizer can update match metrics (e.g., scores, timers) for any participant or group.<br/>
        All actions are logged for transparency and audit purposes.
    ",
    'allowMetricEdits' => "
        Allow authorized users (participants or organizers) to edit or reset previously submitted metrics.<br/>
        For example, resetting the timer or changing a submitted score.<br/>
        Edits are tracked via audit logs.
    ",
    'roundAutoCalculateDescription' => "
        The system evenly divides the tournament start/end window across all rounds to set round durations automatically. <br/>Note: This option can only be activated if Availability setting is enabled.
    ",
    'manualRoundDurationDescription' => "
        Manually set duration for each round.
    ",
    'autoAdvanceByTimerEndDescription' => "
        When enabled, the system automatically determines advancing participants when the timer expires. Otherwise, the host must manually advance participants.
    ",
    'requireScoresBeforeDeadlineDescription' => "
        When enabled, hosts and participants must submit all scores before the timer ends. Afterward, scores are locked and cannot be changed.
    ",
    'startTimerManuallyDescription' => "
        If enabled, the timer won’t start automatically when the round is created. The host must press “Start Timer” in the round dashboard.
    ",
];