<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource\RelationManagers;
use App\Models\Category;
use App\Models\Post;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\EditAction;
use Filament\Actions\Modal\Actions\Action;
use Filament\Tables\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
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

                        Select::make('category_id')
                            ->label('Category')
                            // ->options(Category::all()->pluck('name', 'id'))
                            ->relationship('category', 'name')
                            ->searchable()
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
                        Section::make('Authors')
                            ->schema([
                                Select::make('authors')
                                    ->label('Select Authors')
                                    ->relationship('authors', 'name')
                                    ->multiple()
                                    ->required()
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('Post ID')
                    ->sortable()
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                ImageColumn::make('thumbnail')
                    ->toggleable(),
                ColorColumn::make('color')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('title')
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable(),
                TextColumn::make('slug')
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('category.name')
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable(),
                TextColumn::make('tags')
                    ->searchable(isIndividual: true)
                    ->toggleable(isToggledHiddenByDefault: true),
                CheckboxColumn::make('published')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Published At')
                    ->date('d/m/Y H:i')
                    ->sortable()
                    ->searchable(isIndividual: true)
                    ->toggleable(),
            ])
            ->filters([
                //
            ])
            ->actions(
                [
                    ActionGroup::make([
                        ViewAction::make(),
                        EditAction::make(),
                        DeleteAction::make(),
                    ])
                        ->button()
                        ->label('Actions'),
                ],
                position: ActionsPosition::BeforeColumns
            )
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPosts::route('/'),
            'create' => Pages\CreatePost::route('/create'),
            'edit' => Pages\EditPost::route('/{record}/edit'),
        ];
    }
}
