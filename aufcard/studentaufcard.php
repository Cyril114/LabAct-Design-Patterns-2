<?php

class CallingCard
{
    private $image;
    private array $colors = [];
    private int $width;
    private int $height;

    public function __construct(int $width, int $height)
    {
        $this->width  = $width;
        $this->height = $height;
        $this->image  = imagecreatetruecolor($width, $height);
        $this->registerColors();
    }

    private function registerColors(): void
    {
        $this->colors['background'] = imagecolorallocate($this->image, 245, 247, 250);
        $this->colors['white']      = imagecolorallocate($this->image, 255, 255, 255);
        $this->colors['black']      = imagecolorallocate($this->image, 30, 30, 30);
        $this->colors['gray']       = imagecolorallocate($this->image, 100, 100, 100);
        $this->colors['blue']       = imagecolorallocate($this->image, 40, 100, 200);
    }




    public function drawBackground(): void
    {
        imagefill($this->image, 0, 0, $this->colors['background']);
    }

    public function drawCardPanel(): void
    {
        imagefilledrectangle($this->image, 50, 50, 950, 550, $this->colors['white']);
    }

    public function drawAccentBar(): void
    {
        imagefilledrectangle($this->image, 50, 50, 75, 550, $this->colors['blue']);
    }

    public function drawBusinessName(string $businessName): void
    {
        imagestring($this->image, 5, 120, 100, strtoupper($businessName), $this->colors['blue']);
    }

    public function drawPosition(string $position): void
    {
        imagestring($this->image, 4, 120, 210, $position, $this->colors['blue']);
    }

    public function drawDivider(): void
    {
        imageline($this->image, 120, 260, 880, 260, $this->colors['gray']);
    }

    public function drawWebsite(string $url): void
    {
        imagestring($this->image, 3, 120, 485, $url, $this->colors['gray']);
    }


    public function drawPersonName(string $name): void
    {
        imagestring($this->image, 5, 120, 170, $name, $this->colors['black']);
    }

    public function drawContactInfo(string $email, string $phone, string $address): void
    {
        imagestring($this->image, 4, 120, 310, 'Email: ' . $email, $this->colors['black']);
        imagestring($this->image, 4, 120, 365, 'Phone: ' . $phone, $this->colors['black']);
        imagestring($this->image, 4, 120, 420, 'Address: ' . $address, $this->colors['black']);
    }

    public function save(string $path): bool
    {
        return imagepng($this->image, $path);
    }

    public function __clone(): void
    {
        $copy = imagecreatetruecolor($this->width, $this->height);
        imagecopy($copy, $this->image, 0, 0, 0, 0, $this->width, $this->height);
        $this->image = $copy;
    }

    public function __destruct()
    {
        if ($this->image) {
            imagedestroy($this->image);
        }
    }
}

class CallingCardTemplateRegistry
{
    private CallingCard $template;

    public function __construct(CallingCard $template)
    {
        $this->template = $template;
    }

    public function getBlankCard(): CallingCard
    {
        return clone $this->template;
    }
}


$template = new CallingCard(1000, 600);
$template->drawBackground();
$template->drawCardPanel();
$template->drawAccentBar();
$template->drawBusinessName('College of Computing Studies');
$template->drawPosition('BSIT Student');
$template->drawDivider();
$template->drawWebsite('www.auf.edu.ph');

$registry = new CallingCardTemplateRegistry($template);

$students = [
    ['first_name' => 'Felicity', 'last_name' => 'Hampton'],
    ['first_name' => 'Hank', 'last_name' => 'Rice'],
    ['first_name' => 'Ada', 'last_name' => 'Wilson'],
    ['first_name' => 'Daniel', 'last_name' => 'Salgado'],
    ['first_name' => 'Avalynn', 'last_name' => 'Crane'],
    ['first_name' => 'Fox', 'last_name' => 'Summers'],
    ['first_name' => 'Frankie', 'last_name' => 'Andersen'],
    ['first_name' => 'Alistair', 'last_name' => 'Decker'],
    ['first_name' => 'Aleena', 'last_name' => 'Phillips'],
    ['first_name' => 'Andrew', 'last_name' => 'Marks'],
    ['first_name' => 'Monica', 'last_name' => 'French'],
    ['first_name' => 'Corey', 'last_name' => 'Hess'],
    ['first_name' => 'Kaliyah', 'last_name' => 'Richard'],
    ['first_name' => 'Ahmed', 'last_name' => 'Richardson'],
    ['first_name' => 'Allison', 'last_name' => 'Cortes'],
    ['first_name' => 'Banks', 'last_name' => 'McGee'],
    ['first_name' => 'Kayleigh', 'last_name' => 'Mendoza'],
    ['first_name' => 'Dominic', 'last_name' => 'Atkins'],
    ['first_name' => 'Mina', 'last_name' => 'Beasley'],
    ['first_name' => 'Stanley', 'last_name' => 'Jefferson'],
];

$outputDirectory = __DIR__ . '/cards';

if (!is_dir($outputDirectory)) {
    mkdir($outputDirectory, 0755, true);
}

foreach ($students as $student) {

    $card = $registry->getBlankCard();

    $firstName = $student['first_name'];
    $lastName  = $student['last_name'];

    $name    = "$firstName $lastName";
    $email   = strtolower("{$lastName}.{$firstName}@auf.edu.ph");
    $phone   = sprintf('+1 (555) %03d-%04d', rand(100, 999), rand(1000, 9999));
    $address = 'AUF CCS Building, Angeles City';

    $card->drawPersonName($name);
    $card->drawContactInfo($email, $phone, $address);

    $slug     = strtolower($firstName . '-' . $lastName);
    $filename = $outputDirectory . "/calling-card-{$slug}.png";

    if ($card->save($filename)) {
        echo "Generated: $filename\n";
    } else {
        echo "ERROR: Could not save card for $name\n";
    }
}