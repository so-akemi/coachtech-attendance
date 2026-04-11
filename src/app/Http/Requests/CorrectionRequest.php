<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CorrectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    protected function prepareForValidation()
    {
        // フォームから送られてきた日付（hiddenにあるはず）
        $date = $this->date;

        // 出勤・退勤を「2026-04-08 18:00」のような形式に一時的に書き換え
        if ($this->start_time) {
            $this->merge(['start_datetime' => $date . ' ' . $this->start_time]);
        }
        if ($this->end_time) {
            $this->merge(['end_datetime' => $date . ' ' . $this->end_time]);
        }
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'start_time' => ['required'],
            'end_time'   => ['required', 'after:start_time'], // 出勤より後
            'note'       => ['required'], // 備考（申請理由）
            'rests.*.start_time' => [
                'required',
                'after:start_time', // 出勤時間より前ならエラー
                'before:end_time',   // 退勤時間より後ならエラー
            ],
            'rests.*.end_time' => [
                'required',
                'after:rests.*.start_time', // 休憩終了が開始より前ならエラー
                'before:end_time',          // 退勤時間より後ならエラー
            ],
        ];
    }

    public function messages()
    {
        return [
            // 1. 出勤・退勤の矛盾
            'start_time.required' => '出勤時間もしくは退勤時間が不適切な値です',
            'end_time.required'   => '出勤時間もしくは退勤時間が不適切な値です',
            'end_time.after'      => '出勤時間もしくは退勤時間が不適切な値です',

            // 2. 休憩開始の矛盾（出勤前・退勤後） -> 「休憩時間が不適切な値です」
            'rests.*.start_time.required' => '休憩時間が不適切な値です',
            'rests.*.start_time.after'    => '休憩時間が不適切な値です',
            'rests.*.start_time.before'   => '休憩時間が不適切な値です',

            // 3. 休憩終了の矛盾（退勤後など） -> 「休憩時間もしくは退勤時間が不適切な値です」
            'rests.*.end_time.required' => '休憩時間もしくは退勤時間が不適切な値です',
            'rests.*.end_time.after'    => '休憩時間もしくは退勤時間が不適切な値です',
            'rests.*.end_time.before'   => '休憩時間もしくは退勤時間が不適切な値です',

            // 4. 備考
            'note.required' => '備考を記入してください',
        ];
    }
}
