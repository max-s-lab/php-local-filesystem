<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__)
    ->exclude([
        'vendor'
    ]);

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
        'align_multiline_comment' => true,
        'array_indentation' => true,
        'array_syntax' => ['syntax' => 'short'],
        'assign_null_coalescing_to_coalesce_equal' => true,
        'binary_operator_spaces' => true,
        'blank_line_before_statement' => true,
        'braces_position' => ['allow_single_line_anonymous_functions' => false],
        'cast_spaces' => true,
        'concat_space' => ['spacing' => 'one'],
        'class_attributes_separation' => [
            'elements' => [
                'method' => 'one',
                'property' => 'one',
            ],
        ],
        'class_reference_name_casing' => true,
        'clean_namespace' => true,
        'combine_consecutive_issets' => true,
        'combine_consecutive_unsets' => true,
        'explicit_string_variable' => true,
        'increment_style' => ['style' => 'post'],
        'list_syntax' => ['syntax' => 'short'],
        'magic_constant_casing' => true,
        'method_chaining_indentation' => true,
        'multiline_comment_opening_closing' => true,
        'native_function_casing' => true,
        'native_type_declaration_casing' => true,
        'new_with_parentheses' => ['anonymous_class' => false],
        'no_alternative_syntax' => true,
        'no_extra_blank_lines' => [
            'tokens' => [
                'curly_brace_block',
                'parenthesis_brace_block',
                'square_brace_block',
            ]
        ],
        'no_leading_namespace_whitespace' => true,
        'no_multiline_whitespace_around_double_arrow' => true,
        'no_short_bool_cast' => true,
        'no_singleline_whitespace_before_semicolons' => true,
        'no_unneeded_import_alias' => true,
        'no_unneeded_control_parentheses' => true,
        'no_unused_imports' => true,
        'no_useless_concat_operator' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'normalize_index_brace' => true,
        'nullable_type_declaration' => ['syntax' => 'question_mark'],
        'object_operator_without_whitespace' => true,
        'ordered_class_elements' => true,
        'operator_linebreak' => [
            'only_booleans' => true,
            'position' => 'end',
        ],
        'return_assignment' => true,
        'semicolon_after_instruction' => true,
        'self_static_accessor' => true,
        'single_line_empty_body' => true,
        'single_line_comment_spacing' => true,
        'single_line_comment_style' => ['comment_types' => ['hash']],
        'single_quote' => true,
        'standardize_increment' => true,
        'ternary_to_null_coalescing' => true,
        'trailing_comma_in_multiline' => true,
        'trim_array_spaces' => true,
        'yoda_style' => [
            'equal' => false,
            'identical' => false,
            'less_and_greater' => false,
        ],
        'function_declaration' => ['closure_fn_spacing' => 'none'],
        'php_unit_set_up_tear_down_visibility' => true,
        'php_unit_data_provider_static' => true,
        'phpdoc_var_annotation_correct_order' => true,
        'phpdoc_order_by_value' => true,
        'phpdoc_no_empty_return' => true,
        'protected_to_private' => true,
        'whitespace_after_comma_in_array' => ['ensure_single_space' => true],
    ])
    ->setRiskyAllowed(true)
    ->setFinder($finder);
