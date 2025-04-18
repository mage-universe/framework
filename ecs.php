<?php declare(strict_types=1);

use PHP_CodeSniffer\Standards\Generic\Sniffs\Files\LineLengthSniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\Metrics\CyclomaticComplexitySniff;
use PHP_CodeSniffer\Standards\Generic\Sniffs\PHP\ForbiddenFunctionsSniff;
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ArrayNotation\TrimArraySpacesFixer;
use PhpCsFixer\Fixer\Basic\SingleLineEmptyBodyFixer;
use PhpCsFixer\Fixer\Casing\ClassReferenceNameCasingFixer;
use PhpCsFixer\Fixer\Casing\LowercaseStaticReferenceFixer;
use PhpCsFixer\Fixer\Casing\MagicMethodCasingFixer;
use PhpCsFixer\Fixer\Casing\NativeFunctionCasingFixer;
use PhpCsFixer\Fixer\Casing\NativeFunctionTypeDeclarationCasingFixer;
use PhpCsFixer\Fixer\CastNotation\CastSpacesFixer;
use PhpCsFixer\Fixer\ClassNotation\FinalClassFixer;
use PhpCsFixer\Fixer\ClassNotation\NoBlankLinesAfterClassOpeningFixer;
use PhpCsFixer\Fixer\ClassNotation\NoNullPropertyInitializationFixer;
use PhpCsFixer\Fixer\ClassNotation\NoUnneededFinalMethodFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedTypesFixer;
use PhpCsFixer\Fixer\ClassNotation\ProtectedToPrivateFixer;
use PhpCsFixer\Fixer\ClassNotation\SelfAccessorFixer;
use PhpCsFixer\Fixer\ClassNotation\SelfStaticAccessorFixer;
use PhpCsFixer\Fixer\ClassNotation\VisibilityRequiredFixer;
use PhpCsFixer\Fixer\ClassUsage\DateTimeImmutableFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUnneededControlParenthesesFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUnneededCurlyBracesFixer;
use PhpCsFixer\Fixer\ControlStructure\NoUselessElseFixer;
use PhpCsFixer\Fixer\ControlStructure\SimplifiedIfReturnFixer;
use PhpCsFixer\Fixer\ControlStructure\TrailingCommaInMultilineFixer;
use PhpCsFixer\Fixer\ControlStructure\YodaStyleFixer;
use PhpCsFixer\Fixer\Import\FullyQualifiedStrictTypesFixer;
use PhpCsFixer\Fixer\Import\GlobalNamespaceImportFixer;
use PhpCsFixer\Fixer\Import\NoLeadingImportSlashFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Operator\AssignNullCoalescingToCoalesceEqualFixer;
use PhpCsFixer\Fixer\Operator\NoUselessConcatOperatorFixer;
use PhpCsFixer\Fixer\Operator\NoUselessNullsafeOperatorFixer;
use PhpCsFixer\Fixer\Operator\ObjectOperatorWithoutWhitespaceFixer;
use PhpCsFixer\Fixer\Operator\TernaryToElvisOperatorFixer;
use PhpCsFixer\Fixer\Operator\TernaryToNullCoalescingFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitConstructFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitDedicateAssertFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitDedicateAssertInternalTypeFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitExpectationFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitMethodCasingFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\Strict\StrictComparisonFixer;
use PhpCsFixer\Fixer\StringNotation\SingleQuoteFixer;
use PhpCsFixer\Fixer\Whitespace\ArrayIndentationFixer;
use PhpCsFixer\Fixer\Whitespace\StatementIndentationFixer;
use PhpCsFixer\Fixer\Whitespace\TypeDeclarationSpacesFixer;
use PhpCsFixer\Fixer\Whitespace\TypesSpacesFixer;
use Symplify\CodingStandard\Fixer\LineLength\LineLengthFixer;
use Symplify\CodingStandard\Fixer\Strict\BlankLineAfterStrictTypesFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return ECSConfig::configure()
    ->withParallel()
    ->withPaths([__DIR__ . '/src', __DIR__ . '/tests'])
    ->withSets([SetList::PSR_12])
    ->withRules([
        // Imports
        NoUnusedImportsFixer::class,
        FullyQualifiedStrictTypesFixer::class,
        GlobalNamespaceImportFixer::class,
        NoLeadingImportSlashFixer::class,
        // Arrays
        TrimArraySpacesFixer::class,
        ArrayIndentationFixer::class,
        // Blank lines
        BlankLineAfterStrictTypesFixer::class,
        NoBlankLinesAfterClassOpeningFixer::class,
        // Spacing
        SingleLineEmptyBodyFixer::class,
        CastSpacesFixer::class,
        TypeDeclarationSpacesFixer::class,
        TypesSpacesFixer::class,
        // Casing
        ClassReferenceNameCasingFixer::class,
        LowercaseStaticReferenceFixer::class,
        MagicMethodCasingFixer::class,
        NativeFunctionCasingFixer::class,
        NativeFunctionTypeDeclarationCasingFixer::class,
        // Architecture
        //FinalClassFixer::class,
        ProtectedToPrivateFixer::class,
        VisibilityRequiredFixer::class,
        DateTimeImmutableFixer::class,
        NoUselessElseFixer::class,
        // Operator
        AssignNullCoalescingToCoalesceEqualFixer::class,
        NoUselessConcatOperatorFixer::class,
        NoUselessNullsafeOperatorFixer::class,
        ObjectOperatorWithoutWhitespaceFixer::class,
        TernaryToElvisOperatorFixer::class,
        TernaryToNullCoalescingFixer::class,
        // Testing
        PhpUnitConstructFixer::class,
        PhpUnitDedicateAssertFixer::class,
        PhpUnitDedicateAssertInternalTypeFixer::class,
        PhpUnitExpectationFixer::class,
        // Other
        LineLengthFixer::class,
        NoNullPropertyInitializationFixer::class,
        NoUnneededFinalMethodFixer::class,
        SelfAccessorFixer::class,
        SelfStaticAccessorFixer::class,
        NoUnneededControlParenthesesFixer::class,
        NoUnneededCurlyBracesFixer::class,
        SimplifiedIfReturnFixer::class,
        TrailingCommaInMultilineFixer::class,
        DeclareStrictTypesFixer::class,
        StrictComparisonFixer::class,
        SingleQuoteFixer::class,
        StatementIndentationFixer::class,
    ])
    ->withSkip([
        \PhpCsFixer\Fixer\ClassNotation\ClassAttributesSeparationFixer::class,
        \PhpCsFixer\Fixer\PhpTag\BlankLineAfterOpeningTagFixer::class,
        \Symplify\CodingStandard\Fixer\Spacing\MethodChainingNewlineFixer::class
    ])
    ->withConfiguredRule(ArraySyntaxFixer::class, ['syntax' => 'short'])
    ->withConfiguredRule(OrderedClassElementsFixer::class, [
        'order' => [
            'use_trait',
            'constant_public',
            'constant_protected',
            'constant_private',
            'property_public',
            'property_protected',
            'property_private',
            'construct',
            'destruct',
        ]
    ])
    ->withConfiguredRule(LineLengthSniff::class, [
        'lineLimit' => 100,
        'absoluteLineLimit' => 120
    ])
    ->withConfiguredRule(ForbiddenFunctionsSniff::class, [
        'forbiddenFunctions' => [
            'dd' => null,
            'dump' => null
        ]
    ])
    ->withConfiguredRule(CyclomaticComplexitySniff::class, [
        'absoluteComplexity' => 20
    ])
    ->withConfiguredRule(
        YodaStyleFixer::class,
        ['equal' => false, 'identical' => false, 'less_and_greater' => false]
    )
    ->withConfiguredRule(PhpUnitMethodCasingFixer::class, ['case' => PhpUnitMethodCasingFixer::SNAKE_CASE])
    ->withConfiguredRule(OrderedTypesFixer::class, ['null_adjustment' => 'always_last']);
