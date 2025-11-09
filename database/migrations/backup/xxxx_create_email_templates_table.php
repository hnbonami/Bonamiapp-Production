Schema::create('email_templates', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('slug')->unique();
    $table->string('type'); // 'klant', 'medewerker', 'algemeen'
    $table->string('subject');
    $table->text('content');
    $table->text('description')->nullable();
    $table->boolean('is_active')->default(true);
    $table->foreignId('organisatie_id')->nullable()->constrained('organisaties')->onDelete('cascade');
    $table->timestamps();
});