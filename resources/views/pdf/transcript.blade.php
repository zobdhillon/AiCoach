<!DOCTYPE html>
<html>

    <head>
        <meta charset="utf-8">
        <style>
            @page {
                margin-top: 90px;
                margin-bottom: 40px;
                margin-left: 0;
                margin-right: 0;
            }

            @page :first {
                margin-top: 0;
                margin-bottom: 40px;
            }

            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: DejaVu Sans, sans-serif;
                font-size: 11px;
                color: #0d1f2d;
                background: #ffffff;
                padding-top: 60px;
            }

            .header {
                margin-top: -60px;
                background: #0f7c6e;
                padding: 32px 40px 28px;
                color: white;
            }

            .header-label {
                font-size: 9px;
                font-weight: bold;
                text-transform: uppercase;
                letter-spacing: .12em;
                opacity: .7;
                margin-bottom: 6px;
            }

            .header-title {
                font-size: 22px;
                font-weight: bold;
                margin-bottom: 6px;
                line-height: 1.2;
            }

            .header-meta {
                font-size: 10px;
                opacity: .65;
            }

            .body {
                padding: 28px 40px;
            }

            .section-header-table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 14px;
            }

            .section-bar {
                width: 4px;
                background-color: #0f7c6e;
                font-size: 1px;
                line-height: 1px;
                padding: 0;
            }

            .section-title {
                padding-left: 10px;
                font-size: 9px;
                font-weight: bold;
                text-transform: uppercase;
                letter-spacing: .1em;
                color: #0f7c6e;
                vertical-align: middle;
                padding-top: 0;
                padding-bottom: 0;
            }

            .message {
                margin-bottom: 14px;
                padding-left: 12px;
                page-break-inside: avoid;
            }

            .message-role {
                font-size: 8px;
                font-weight: bold;
                text-transform: uppercase;
                letter-spacing: .08em;
                margin-bottom: 3px;
                color: #6b8299;
            }

            .message.user .message-role {
                color: #0f7c6e;
            }

            .message-bubble {
                background: #ffffff;
                border-left: 3px solid #e8eef3;
                padding: 6px 0 6px 12px;
                line-height: 1.6;
                color: #3d5166;
            }

            .message.user .message-bubble {
                background: #ffffff;
                border-left: 3px solid #0f7c6e;
                color: #0d1f2d;
            }

            .breakdown-row {
                margin-bottom: 12px;
            }

            .feedback-box {
                background: #ffffff;
                border: 1px solid #0f7c6e;
                border-radius: 6px;
                padding: 14px 16px;
            }

            .feedback-text {
                line-height: 1.6;
                color: #3d5166;
                font-size: 11px;
            }

            .divider {
                height: 1px;
                background: #e8eef3;
                margin: 20px 0;
            }
        </style>
    </head>

    <body>

        {{-- Header --}}
        <div class="header">
            <div class="header-label">Session Report · Rehearse AI Coach</div>
            <div class="header-title">{{ $scenario->title }}</div>
            <div class="header-meta">
                Session #{{ $conversation->id }} &nbsp;·&nbsp;
                {{ $conversation->created_at->format('F j, Y \a\t g:ia') }}
            </div>
        </div>

        {{-- Score hero --}}
        @if ($scores)
            @php
                // FORCE convert to integer and handle edge cases
                $finalScore = intval($scores['final']);

                // Check if it's a low score (below 40)
$isLowScore = $finalScore < 40;
$accentColor = $isLowScore ? '#d9534f' : '#0f7c6e';

// Performance label
$performanceLabel =
    $finalScore >= 80 ? 'Excellent' : ($finalScore >= 60 ? 'Good Effort' : 'Keep Practising');
            @endphp

            <table
                style="width: 100%; border: 1px solid {{ $isLowScore ? '#f5c6c2' : '#d0eeeb' }}; border-collapse: collapse; margin-bottom: 20px; background-color: #ffffff;">
                <tr>
                    <td style="padding: 20px 0 20px 40px; width: 80px; vertical-align: middle;">
                        <table
                            style="border: 2px solid {{ $accentColor }}; border-collapse: collapse; background-color: #ffffff; width: 70px; height: 70px; text-align: center;">
                            <tr>
                                <td
                                    style="vertical-align: middle; text-align: center; font-size: 28px; font-weight: bold; color: {{ $accentColor }}; height: 70px; padding: 0;">
                                    {{ $finalScore }}
                                </td>
                            </tr>
                        </table>
                    </td>
                    <td style="padding: 20px 24px 20px 16px; vertical-align: middle;">
                        <div
                            style="font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: .1em; color: {{ $accentColor }}; margin-bottom: 4px;">
                            Overall Performance
                            @if ($isLowScore)
                                <span
                                    style="color: #d9534f; margin-left: 8px; font-size: 8px; font-weight: bold; text-transform: uppercase; letter-spacing: .06em;">(Needs
                                    Improvement)</span>
                            @endif
                        </div>
                        <div style="font-size: 16px; font-weight: bold; color: #0d1f2d; margin-bottom: 12px;">
                            {{ $performanceLabel }}
                            @if ($isLowScore)
                                <span style="font-size: 12px; font-weight: normal; color: #d9534f; margin-left: 8px;">⚠️
                                    Below target</span>
                            @endif
                        </div>
                        <table style="border-collapse: collapse;">
                            <tr>
                                @foreach (['clarity' => 'Clarity', 'confidence' => 'Confidence', 'objective' => 'Objective', 'adaptability' => 'Adaptability'] as $key => $label)
                                    @php
                                        $subScore = intval($scores[$key] ?? 0);
                                        $isSubLow = $subScore < 40;
                                        $subColor = $isSubLow ? '#d9534f' : '#0f7c6e';
                                    @endphp
                                    <td style="text-align: center; padding-right: 20px; padding-bottom: 4px;">
                                        <div style="font-size: 14px; font-weight: bold; color: {{ $subColor }};">
                                            {{ $subScore }}
                                        </div>
                                        <div
                                            style="font-size: 9px; color: #6b8299; text-transform: uppercase; letter-spacing: .06em; margin-top: 2px;">
                                            {{ $label }}
                                        </div>
                                    </td>
                                @endforeach
                            </tr>
                        </table>
                        @if (!empty($scores['completion_rate']) && $scores['completion_rate'] < 100)
                            <div
                                style="margin-top: 10px; color: {{ $isLowScore ? '#d9534f' : '#0f7c6e' }}; font-size: 9px; font-weight: bold; text-transform: uppercase; letter-spacing: .06em;">
                                {{ $scores['completion_rate'] }}% session completed
                            </div>
                        @endif
                    </td>
                </tr>
            </table>
        @endif

        <div class="body">
            @php $firstSection = true; @endphp

            @if ($scores)
                {{-- Performance Breakdown --}}
                <table class="section-header-table" style="margin-top: {{ $firstSection ? '0' : '28px' }};">
                    <tr>
                        <td class="section-bar">&nbsp;</td>
                        <td class="section-title">Performance Breakdown</td>
                    </tr>
                </table>
                @php $firstSection = false; @endphp

                @foreach (['clarity' => 'Clarity', 'confidence' => 'Confidence', 'objective' => 'Objective', 'adaptability' => 'Adaptability'] as $key => $label)
                    @php
                        $subScore = intval($scores[$key] ?? 0);
                        $isSubLow = $subScore < 40;
                        $barColor = $isSubLow ? '#d9534f' : '#0f7c6e';
                        $scoreColor = $isSubLow ? '#d9534f' : '#0f7c6e';
                    @endphp
                    <div class="breakdown-row">
                        <table style="width: 100%; border-collapse: collapse; margin-bottom: 4px;">
                            <tr>
                                <td style="font-size: 10px; font-weight: bold; color: #3d5166; padding: 0;">
                                    {{ $label }}
                                    @if ($isSubLow)
                                        <span style="color:#d9534f;font-size:8px;margin-left:4px;">⚠️</span>
                                    @endif
                                </td>
                                <td
                                    style="text-align: right; font-size: 10px; font-weight: bold; color: {{ $scoreColor }}; padding: 0;">
                                    {{ $subScore }}/100
                                </td>
                            </tr>
                        </table>
                        <table
                            style="width: 100%; height: 7px; border-collapse: collapse; background-color: #e8eef3; border-radius: 4px; overflow: hidden; margin-bottom: 12px;">
                            <tr>
                                @if ($subScore > 0)
                                    <td
                                        style="width: {{ $subScore }}%; background-color: {{ $barColor }}; height: 7px; padding: 0;">
                                    </td>
                                @endif
                                @if ($subScore < 100)
                                    <td
                                        style="width: {{ 100 - $subScore }}%; background-color: #e8eef3; height: 7px; padding: 0;">
                                    </td>
                                @endif
                            </tr>
                        </table>
                    </div>
                @endforeach

                <div class="divider"></div>

                {{-- Coach Feedback --}}
                @if (!empty($scores['feedback']))
                    <table class="section-header-table" style="margin-top: {{ $firstSection ? '0' : '28px' }};">
                        <tr>
                            <td class="section-bar">&nbsp;</td>
                            <td class="section-title">Coach Feedback</td>
                        </tr>
                    </table>
                    @php $firstSection = false; @endphp

                    <div class="feedback-box" style="border-color: {{ $isLowScore ? '#d9534f' : '#0f7c6e' }};">
                        <p class="feedback-text">{{ $scores['feedback'] }}</p>
                        @if ($isLowScore)
                            <p
                                style="margin-top: 8px; color: #d9534f; font-weight: bold; font-size: 10px; margin-bottom: 0;">
                                💡 Recommendation: Consider reviewing the fundamentals and practicing with our guided
                                modules.
                            </p>
                        @endif
                    </div>
                    <div class="divider"></div>
                @endif
            @endif

            {{-- Conversation Transcript --}}
            <table class="section-header-table" style="margin-top: {{ $firstSection ? '0' : '28px' }};">
                <tr>
                    <td class="section-bar">&nbsp;</td>
                    <td class="section-title">Conversation Transcript</td>
                </tr>
            </table>
            @php $firstSection = false; @endphp

            @foreach ($messages as $msg)
                @if ($msg->role !== 'system')
                    <div class="message {{ $msg->role }}">
                        <div class="message-role">{{ $msg->role === 'user' ? 'You' : 'Interviewer' }}</div>
                        <div class="message-bubble">{{ $msg->content }}</div>
                    </div>
                @endif
            @endforeach

            {{-- Footer --}}
            <table
                style="width: 100%; border-collapse: collapse; margin-top: 36px; border-top: 1px solid #e8eef3; font-size: 9px; color: #6b8299;">
                <tr>
                    <td style="padding-top: 14px; text-align: left;">
                        {{ $scenario->title }} · Session #{{ $conversation->id }}
                    </td>
                    <td style="padding-top: 14px; text-align: right;">
                        Rehearse AI Coach · Generated {{ now()->format('F j, Y') }}
                    </td>
                </tr>
            </table>

        </div>
    </body>

</html>
