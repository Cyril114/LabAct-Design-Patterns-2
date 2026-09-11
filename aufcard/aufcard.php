<?php

class CallingCard
{
    private $image;
    private array $colors = [];

    public function __construct(int $width, int $height)
    {
        $this->image = imagecreatetruecolor($width, $height);
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

    public function getImage()
    {
        return $this->image;
    }

    public function getColor(string $name)
    {
        return $this->colors[$name];
    }

    public function save(string $path): bool
    {
        return imagepng($this->image, $path);
    }

    public function __destruct()
    {
        if ($this->image) {
            imagedestroy($this->image);
        }
    }
}


interface CallingCardBuilder
{
    public function reset(): void;
    public function buildBackground(): void;
    public function buildCardPanel(): void;
    public function buildAccentBar(): void;
    public function buildBusinessName(string $businessName): void;
    public function buildPersonName(string $name): void;
    public function buildPosition(string $position): void;
    public function buildDivider(): void;
    public function buildContactInfo(string $email, string $phone, string $address): void;
    public function buildWebsite(string $url): void;
    public function getCard(): CallingCard;
}


class GDCallingCardBuilder implements CallingCardBuilder
{
    private CallingCard $card;
    private int $width;
    private int $height;

    public function __construct(int $width = 1000, int $height = 600)
    {
        $this->width = $width;
        $this->height = $height;
        $this->reset();
    }

    public function reset(): void
    {
        $this->card = new CallingCard($this->width, $this->height);
    }

    public function buildBackground(): void
    {
        imagefill($this->card->getImage(), 0, 0, $this->card->getColor('background'));
    }

    public function buildCardPanel(): void
    {
        imagefilledrectangle(
            $this->card->getImage(),
            50, 50,
            950, 550,
            $this->card->getColor('white')
        );
    }

    public function buildAccentBar(): void
    {
        imagefilledrectangle(
            $this->card->getImage(),
            50, 50,
            75, 550,
            $this->card->getColor('blue')
        );
    }

    public function buildBusinessName(string $businessName): void
    {
        imagestring(
            $this->card->getImage(),
            5,
            120, 100,
            strtoupper($businessName),
            $this->card->getColor('blue')
        );
    }

    public function buildPersonName(string $name): void
    {
        imagestring(
            $this->card->getImage(),
            5,
            120, 170,
            $name,
            $this->card->getColor('black')
        );
    }

    public function buildPosition(string $position): void
    {
        imagestring(
            $this->card->getImage(),
            4,
            120, 210,
            $position,
            $this->card->getColor('blue')
        );
    }

    public function buildDivider(): void
    {
        imageline(
            $this->card->getImage(),
            120, 260,
            880, 260,
            $this->card->getColor('gray')
        );
    }

    public function buildContactInfo(string $email, string $phone, string $address): void
    {
        $image = $this->card->getImage();
        $black = $this->card->getColor('black');

        imagestring($image, 4, 120, 310, 'Email: ' . $email, $black);
        imagestring($image, 4, 120, 365, 'Phone: ' . $phone, $black);
        imagestring($image, 4, 120, 420, 'Address: ' . $address, $black);
    }

    public function buildWebsite(string $url): void
    {
        imagestring(
            $this->card->getImage(),
            3,
            120, 485,
            $url,
            $this->card->getColor('gray')
        );
    }

    
    public function getCard(): CallingCard
    {
        $card = $this->card;
        $this->reset();
        return $card;
    }
}



class CallingCardDirector
{
    private CallingCardBuilder $builder;

    public function __construct(CallingCardBuilder $builder)
    {
        $this->builder = $builder;
    }

    public function setBuilder(CallingCardBuilder $builder): void
    {
        $this->builder = $builder;
    }

    public function constructStandardCard(array $data): void
    {
        $this->builder->reset();
        $this->builder->buildBackground();
        $this->builder->buildCardPanel();
        $this->builder->buildAccentBar();
        $this->builder->buildBusinessName($data['businessName']);
        $this->builder->buildPersonName($data['name']);
        $this->builder->buildPosition($data['position']);
        $this->builder->buildDivider();
        $this->builder->buildContactInfo($data['email'], $data['phone'], $data['address']);
        $this->builder->buildWebsite($data['website']);
    }
}


$firstName = "Juan";
$lastName  = "Dela Cruz";

$businessName = "College of Computing Studies";
$position     = "BSIT Student";

$street = "AUF CCS Building";
$city   = "Angeles City";

$name = "$firstName $lastName";

$email = strtolower("{$lastName}.{$firstName}@auf.edu.ph");

$phone = sprintf(
    '+1 (555) %03d-%04d',
    rand(100, 999),
    rand(1000, 9999)
);

$address = "$street, $city";

$data = [
    'businessName' => $businessName,
    'name'         => $name,
    'position'     => $position,
    'email'        => $email,
    'phone'        => $phone,
    'address'      => $address,
    'website'      => 'www.auf.edu.ph',
];

$builder  = new GDCallingCardBuilder(1000, 600);
$director = new CallingCardDirector($builder);

$director->constructStandardCard($data);
$card = $builder->getCard();

$outputDirectory = __DIR__ . '/cards';

if (!is_dir($outputDirectory)) {
    mkdir($outputDirectory, 0755, true);
}

$filename = $outputDirectory . '/calling-card-' . uniqid() . '.png';

if ($card->save($filename)) {
    echo "Calling card generated successfully.\n";
    echo "File: $filename\n";
} else {
    echo "ERROR: Could not save image.\n";
}