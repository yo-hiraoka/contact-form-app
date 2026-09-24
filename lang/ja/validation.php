<?php

return [
    'required' => ':attributeは必須です。',
    'string' => ':attributeは文字列で入力してください。',
    'integer' => ':attributeは整数で入力してください。',
    'array' => ':attributeは配列で入力してください。',
    'email' => ':attributeは有効なメールアドレス形式で入力してください。',
    'date' => ':attributeは有効な日付を入力してください。',
    'in' => '選択された:attributeは正しくありません。',
    'exists' => '選択された:attributeは正しくありません。',
    'unique' => ':attributeはすでに使用されています。',
    'regex' => ':attributeの形式が正しくありません。',

    'min' => [
        'numeric' => ':attributeには:min以上の値を指定してください。',
        'string' => ':attributeは:min文字以上で入力してください。',
        'array' => ':attributeには:min個以上の項目を指定してください。',
    ],

    'max' => [
        'numeric' => ':attributeには:max以下の値を指定してください。',
        'string' => ':attributeは:max文字以内で入力してください。',
        'array' => ':attributeには:max個以下の項目を指定してください。',
    ],

    'attributes' => [
        'first_name' => '名',
        'last_name' => '姓',
        'gender' => '性別',
        'email' => 'メールアドレス',
        'tel' => '電話番号',
        'address' => '住所',
        'building' => '建物名',
        'category_id' => 'お問い合わせの種類',
        'detail' => 'お問い合わせ内容',
        'tag_ids' => 'タグ',
        'keyword' => 'キーワード',
        'date' => '日付',
        'page' => 'ページ番号',
        'per_page' => '1ページあたりの取得件数',
        'name' => 'タグ名',
    ],
];
