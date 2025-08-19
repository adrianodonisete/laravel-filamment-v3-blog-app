<?php

namespace App\Filament\Resources\CategoryResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PostsRelationManager extends RelationManager
{
    protected static string $relationship = 'posts';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Create a Post')
                    ->description('Fill in the details below to create a new post.')
                    ->collapsible()
                    ->columns(2)
                    ->columnSpan(2)
                    ->schema([
                        TextInput::make('title')
                            ->minLength(3)
                            ->maxLength(50)
                            ->required(),
                        TextInput::make('slug')
                            ->minLength(3)
                            ->maxLength(100)
                            ->unique(ignoreRecord: true)
                            ->required(),

                        ColorPicker::make('color')->required(),
                        MarkdownEditor::make('content')
                            ->required()
                            ->columnSpanFull(),
                    ]),

                Group::make()
                    ->schema([
                        Section::make('Image')
                            ->collapsible()
                            ->schema([
                                FileUpload::make('thumbnail')
                                    ->disk('public')
                                    ->directory('thumbnails')
                                    ->required(),
                            ]),

                        Section::make('Meta')
                            ->schema([
                                TagsInput::make('tags')->required(),
                                Checkbox::make('published'),
                            ]),
                    ]),


            ])
            ->columns([
                'md' => 3,
                'lg' => 3,
                'xl' => 3,
                'default' => 1,
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->searchable(isIndividual: true),
                Tables\Columns\TextColumn::make('slug')
                    ->searchable(isIndividual: true),
                Tables\Columns\CheckboxColumn::make('published')
                    ->searchable(isIndividual: true, isGlobal: false),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
