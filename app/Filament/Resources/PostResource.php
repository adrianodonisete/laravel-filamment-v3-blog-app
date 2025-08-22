<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PostResource\Pages;
use App\Filament\Resources\PostResource\RelationManagers;
use App\Models\Category;
use App\Models\Post;
use BladeUI\Icons\Components\Icon;
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
use Filament\Forms\Components\Tabs;
use Filament\Forms\Components\Tabs\Tab;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Support\Enums\IconPosition;
use Filament\Tables;
use Filament\Tables\Columns\CheckboxColumn;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
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
                Tabs::make('Create new Post')
                    ->tabs([
                        Tab::make('Title')
                            ->icon('heroicon-m-inbox')
                            ->iconPosition(IconPosition::After)
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
                            ]),

                        Tab::make('Content')
                            ->icon('heroicon-m-document-text')
                            ->iconPosition(IconPosition::After)
                            ->schema([
                                MarkdownEditor::make('content')
                                    ->required()
                                    ->columnSpanFull(),
                            ]),

                        Tab::make('Meta')
                            ->schema([
                                FileUpload::make('thumbnail')
                                    ->disk('public')
                                    ->directory('thumbnails')
                                    ->required(),

                                TagsInput::make('tags')->required(),
                                Checkbox::make('published'),
                            ]),

                        Tab::make('Authors')
                            ->schema([
                                Select::make('authors')
                                    ->label('Select Authors')
                                    ->relationship('authors', 'name')
                                    ->multiple()
                                    ->required()
                            ]),
                    ])
                    ->columnSpanFull()
                    ->activeTab(1)
                    ->persistTabInQueryString(),
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
                // Filter::make('published')
                //     ->label('Published Posts')
                //     ->query(fn(Builder $query): Builder => $query->where('published', true)),
                // Filter::make('unpublished')
                //     ->label('UnPublished Posts')
                //     ->query(fn(Builder $query): Builder => $query->where('published', false)),

                TernaryFilter::make('published')
                    ->label('Published')
                    ->trueLabel('Yes')
                    ->falseLabel('No')
                // ->query(fn(Builder $query, ?bool $value): Builder => $query->where('published', $value))
                ,

                SelectFilter::make('category_id')
                    ->label('Category')
                    // ->options(Category::all()->pluck('name', 'id'))
                    ->relationship('category', 'name')
                    ->multiple()
                    ->preload(),
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
