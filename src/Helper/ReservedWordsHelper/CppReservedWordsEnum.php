<?php

namespace App\Helper\ReservedWordsHelper;

enum CppReservedWordsEnum: string
{
    case Asm = 'asm';
    case Auto = 'auto';
    case Break = 'break';
    case Case = 'case';
    case Catch = 'catch';
    case Char = 'char';
    case Class1 = 'class';
    case Const = 'const';
    case Continue = 'continue';
    case Default = 'default';
    case Delete = 'delete';
    case Do = 'do';
    case Double = 'double';
    case Else = 'else';
    case Enum = 'enum';
    case Extern = 'extern';
    case Float = 'float';
    case For = 'for';
    case Friend = 'friend';
    case Goto = 'goto';
    case If = 'if';
    case Int = 'int';
    case Long = 'long';
    case New = 'new';
    case Operator = 'operator';
    case Private = 'private';
    case Protected = 'protected';
    case Public = 'public';
    case Register = 'register';
    case Return = 'return';
    case Short = 'short';
    case Signed = 'signed';
    case Sizeof = 'sizeof';
    case Static = 'static';
    case Struct = 'struct';
    case Switch = 'switch';
    case Template = 'template';
    case This = 'this';
    case Throw = 'throw';
    case Try = 'try';
    case Typedef = 'typedef';
    case Union = 'union';
    case Unsigned = 'unsigned';
    case Virtual = 'virtual';
    case Void = 'void';
    case Volatile = 'volatile';
    case While = 'while';


    public static function getAllValuesCpp(): array
    {
        return array_column(CppReservedWordsEnum::cases(), 'value');
    }
}
