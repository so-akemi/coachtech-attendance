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
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],

            // 既存の休憩
            'rests.*.start_time' => ['required_with:rests.*.end_time', 'nullable', 'date_format:H:i', 'after:start_time', 'before:end_time'],
            'rests.*.end_time' => ['required_with:rests.*.start_time', 'nullable', 'date_format:H:i', 'after:rests.*.start_time', 'before:end_time'],

            // 新規の休憩
            'new_rests.*.start_time' => ['required_with:new_rests.*.end_time', 'nullable', 'date_format:H:i', 'after:start_time', 'before:end_time'],
            'new_rests.*.end_time' => ['required_with:new_rests.*.start_time', 'nullable', 'date_format:H:i', 'after:new_rests.*.start_time', 'before:end_time'],

            'note' => ['required', 'string'],
        ];
    }

    public function messages()
    {
        return [
            // 1. 出勤・退勤の整合性
            'end_time.after' => '出勤時間もしくは退勤時間が不適切な値です',

            // 2. 休憩開始が「出勤前」または「退勤後」の場合
            'rests.*.start_time.after' => '休憩時間が不適切な値です',
            'rests.*.start_time.before' => '休憩時間が不適切な値です',
            'new_rests.*.start_time.after' => '休憩時間が不適切な値です',
            'new_rests.*.start_time.before' => '休憩時間が不適切な値です',

            // 3. 休憩終了が「退勤後」の場合（および休憩内整合性）
            'rests.*.end_time.after' => '休憩時間もしくは退勤時間が不適切な値です',
            'rests.*.end_time.before' => '休憩時間もしくは退勤時間が不適切な値です',
            'new_rests.*.end_time.after' => '休憩時間もしくは退勤時間が不適切な値です',
            'new_rests.*.end_time.before' => '休憩時間もしくは退勤時間が不適切な値です',

            // 4. 備考欄未入力
            'note.required' => '備考を記入してください',

            // 補助：未入力時の基本メッセージ
            'start_time.required' => '出勤時間を入力してください',
            'end_time.required' => '退勤時間を入力してください',
            'rests.*.start_time.required_with' => '休憩時間を入力してください',
            'rests.*.end_time.required_with' => '休憩時間を入力してください',
            'new_rests.*.start_time.required_with' => '休憩時間を入力してください',
            'new_rests.*.end_time.required_with' => '休憩時間を入力してください',
        ];
    }
}
