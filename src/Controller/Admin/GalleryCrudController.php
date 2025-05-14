<?php

namespace App\Controller\Admin;

use App\Entity\Gallery;
use App\Entity\Image;
use Doctrine\ORM\EntityManagerInterface;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\SlugField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextEditorField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use EasyCorp\Bundle\EasyAdminBundle\Field\Field;

class GalleryCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return Gallery::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('title'),
            SlugField::new('slug')->setTargetFieldName('title'),
            TextEditorField::new('description'),
            AssociationField::new('category')->setFormTypeOption('by_reference', false),
            AssociationField::new('featuredImage'),
//            AssociationField::new('images')
//                ->setFormTypeOption('by_reference', false)
//                ->hideOnIndex(),

            // Upload multiple images (non-mapped field)
            Field::new('imagesUpload')
                ->setFormType(FileType::class)
                ->setFormTypeOptions([
                    'multiple' => true,
                    'mapped' => false,
                    'required' => false,
                ])
                ->onlyOnForms()
                ->setLabel('Ajouter des images'),
        ];
    }

    public function persistEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->handleUploads($entityInstance);
        parent::persistEntity($entityManager, $entityInstance);
    }

    public function updateEntity(EntityManagerInterface $entityManager, $entityInstance): void
    {
        $this->handleUploads($entityInstance);
        parent::updateEntity($entityManager, $entityInstance);
    }

    private function handleUploads(Gallery $gallery): void
    {
        $request = $this->getContext()->getRequest();
        $files = $request->files->get('Gallery')['imagesUpload'] ?? [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                $image = new Image();
                $image->setImageFile($file);
                $image->setGallery($gallery);
                $gallery->addImage($image);
            }
        }
    }
}
