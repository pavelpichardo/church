<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 0;
            color: #1a1a2e;
        }
        .certificate {
            width: 100%;
            min-height: 100%;
            padding: 50px 70px;
            box-sizing: border-box;
            position: relative;
            background: #fff;
        }
        .border-frame {
            border: 3px double #8b6914;
            padding: 40px 50px;
            min-height: calc(100% - 100px);
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .church-name {
            font-size: 13px;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 5px;
        }
        .church-subtitle {
            font-size: 18px;
            font-weight: bold;
            color: #1a1a2e;
            margin-bottom: 20px;
        }
        .title {
            font-size: 32px;
            font-weight: bold;
            color: #8b6914;
            text-align: center;
            margin: 20px 0 5px;
            letter-spacing: 2px;
        }
        .subtitle {
            font-size: 14px;
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }
        .ornament {
            text-align: center;
            font-size: 20px;
            color: #8b6914;
            margin: 15px 0;
        }
        .body-text {
            font-size: 14px;
            line-height: 2;
            text-align: center;
            margin: 25px 30px;
            color: #333;
        }
        .couple-names {
            font-size: 22px;
            font-weight: bold;
            color: #1a1a2e;
            text-align: center;
            margin: 20px 0;
        }
        .ampersand {
            font-size: 18px;
            color: #8b6914;
            margin: 0 10px;
        }
        .details {
            margin: 30px auto;
            width: 80%;
        }
        .detail-row {
            display: table;
            width: 100%;
            margin-bottom: 8px;
        }
        .detail-label {
            display: table-cell;
            width: 130px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #666;
            padding: 5px 0;
            vertical-align: bottom;
        }
        .detail-value {
            display: table-cell;
            font-size: 14px;
            color: #1a1a2e;
            padding: 5px 0;
            border-bottom: 1px solid #ddd;
            vertical-align: bottom;
        }
        .signatures {
            margin-top: 50px;
            width: 100%;
            display: table;
        }
        .signature-block {
            display: table-cell;
            width: 45%;
            text-align: center;
            padding: 0 20px;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 50px;
            padding-top: 8px;
        }
        .signature-name {
            font-size: 13px;
            font-weight: bold;
            color: #1a1a2e;
        }
        .signature-role {
            font-size: 11px;
            color: #666;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            font-size: 10px;
            color: #999;
        }
    </style>
</head>
<body>
    <div class="certificate">
        <div class="border-frame">
            <div class="header">
                <div class="church-name">Primera Iglesia del Nazareno</div>
                <div class="church-subtitle">"Ven y Ve"</div>
                <div style="font-size: 11px; color: #999;">Columbus, Ohio</div>
            </div>

            <div class="ornament">&mdash; &#10047; &mdash;</div>

            <div class="title">Certificado de Matrimonio</div>
            <div class="subtitle">Marriage Certificate</div>

            <div class="body-text">
                Se certifica que en la fecha indicada, ante Dios y esta congregaci&oacute;n,
                fueron unidos en santo matrimonio:
            </div>

            <div class="couple-names">
                {{ $marriage->spouse1?->first_name }} {{ $marriage->spouse1?->last_name }}
                <span class="ampersand">&amp;</span>
                {{ $marriage->spouse2?->first_name }} {{ $marriage->spouse2?->last_name }}
            </div>

            <div class="details">
                <div class="detail-row">
                    <span class="detail-label">Fecha:</span>
                    <span class="detail-value">{{ $marriage->date->translatedFormat('d \d\e F \d\e Y') }}</span>
                </div>
                <div class="detail-row">
                    <span class="detail-label">Lugar:</span>
                    <span class="detail-value">{{ $marriage->location ?? 'Primera Iglesia del Nazareno "Ven y Ve"' }}</span>
                </div>
                @if($marriage->officiant)
                <div class="detail-row">
                    <span class="detail-label">Oficiante:</span>
                    <span class="detail-value">{{ $marriage->officiant->name }}</span>
                </div>
                @endif
            </div>

            <div class="signatures">
                <div class="signature-block">
                    <div class="signature-line">
                        <div class="signature-name">{{ $marriage->spouse1?->first_name }} {{ $marriage->spouse1?->last_name }}</div>
                        <div class="signature-role">Contrayente</div>
                    </div>
                </div>
                <div class="signature-block" style="float: right;">
                    <div class="signature-line">
                        <div class="signature-name">{{ $marriage->spouse2?->first_name }} {{ $marriage->spouse2?->last_name }}</div>
                        <div class="signature-role">Contrayente</div>
                    </div>
                </div>
            </div>

            <div style="clear: both;"></div>

            @if($marriage->officiant)
            <div style="width: 45%; margin: 30px auto 0; text-align: center;">
                <div class="signature-line">
                    <div class="signature-name">{{ $marriage->officiant->name }}</div>
                    <div class="signature-role">Oficiante</div>
                </div>
            </div>
            @endif

            <div class="footer">
                Certificado No. {{ str_pad($marriage->id, 6, '0', STR_PAD_LEFT) }}
                &bull;
                Emitido el {{ now()->translatedFormat('d \d\e F \d\e Y') }}
            </div>
        </div>
    </div>
</body>
</html>
