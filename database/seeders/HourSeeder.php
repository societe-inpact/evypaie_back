<?php

namespace Database\Seeders;

use App\Models\Hours\Hour;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class HourSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hours = [
            ['code' => 'HS-PHNE', 'label' => 'Pause indemnisee'],
            ['code' => 'HS-PHNE.1', 'label' => 'Pause indemnisee'],
            ['code' => 'HS-HROUTE', 'label' => 'Heures de route'],
            ['code' => 'HS-HEROUTE', 'label' => 'Heures de route'],
            ['code' => 'HS-HMOD1', 'label' => 'Heures normales (modulation)'],
            ['code' => 'HS-PHNE25', 'label' => 'Pause indemnisee 25%'],
            ['code' => 'HS-PHNE25.1', 'label' => 'Pause indemnisee 25%'],
            ['code' => 'HS-PHNE50', 'label' => 'Pause indemnisee 50%'],
            ['code' => 'HS-PHNE50.1', 'label' => 'Pause indemnisee 50%'],
            ['code' => 'HS-PHE', 'label' => 'Heures de pause effectives'],
            ['code' => 'HS-HN', 'label' => 'Heures normales'],
            ['code' => 'HS-HAC15', 'label' => 'Heures avenant contrat 15%'],
            ['code' => 'HS-HAC', 'label' => 'Heures avenant contrat'],
            ['code' => 'HS-HAC12', 'label' => 'Heures avenant contrat 12%'],
            ['code' => 'HS-HAC10', 'label' => 'Heures avenant contrat 10%'],
            ['code' => 'HS-HAC25', 'label' => 'Heures avenant contrat 25%'],
            ['code' => 'HS-HAC05', 'label' => 'Heures avenant contrat 5%'],
            ['code' => 'HS-HAC20', 'label' => 'Heures avenant contrat 20%'],
            ['code' => 'HS-HAC10R', 'label' => 'Heures avenant contrat 10% bonifiees en repos'],
            ['code' => 'HS-HAC07', 'label' => 'Heures avenant contrat 7%'],
            ['code' => 'HS-HAC17', 'label' => 'Heures avenant contrat 17%'],
            ['code' => 'HS-HAC06', 'label' => 'Heures avenant contrat 6%'],
            ['code' => 'HS-HC', 'label' => 'Heures complementaires'],
            ['code' => 'HS-HC-HT', 'label' => 'Heures complementaires non exonerees'],
            ['code' => 'HS-HC-AS', 'label' => 'Heures complementaires/astreinte'],
            ['code' => 'HS-HC05', 'label' => 'Heures complementaires 5%'],
            ['code' => 'HS-HC05-HT', 'label' => 'Heures complementaires 5% non exo'],
            ['code' => 'HS-HC25', 'label' => 'Heures complementaires 25%'],
            ['code' => 'HS-HC25-HT', 'label' => 'Heures complementaires 25% non exo'],
            ['code' => 'HS-HC15', 'label' => 'Heures complementaires 15%'],
            ['code' => 'HS-HC15-HT', 'label' => 'Heures complementaires 15% non exo'],
            ['code' => 'HS-HC10', 'label' => 'Heures complementaires 10%'],
            ['code' => 'HS-HC10-HT', 'label' => 'Heures complementaires 10% non exo'],
            ['code' => 'HS-HC11', 'label' => 'Heures complementaires 11%'],
            ['code' => 'HS-HC10-AS', 'label' => 'Heures complementaires/astreinte 10%'],
            ['code' => 'HS-HC25-AS', 'label' => 'Heures complementaires/astreinte 25%'],
            ['code' => 'HS-HC50-HT', 'label' => 'Heures complementaires 50% non exo'],
            ['code' => 'HS-HC12', 'label' => 'Heures complementaires 12%'],
            ['code' => 'HS-HC20', 'label' => 'Heures complementaires 20%'],
            ['code' => 'HS-HC20-HT', 'label' => 'Heures complementaires 20% non exo'],
            ['code' => 'HS-HC30', 'label' => 'Heures complementaires 30%'],
            ['code' => 'HS-HC30-HT', 'label' => 'Heures complementaires 30% non exo'],
            ['code' => 'HS-HC20-AS', 'label' => 'Heures complementaires/astreinte 20%'],
            ['code' => 'HS-HC17', 'label' => 'Heures complementaires 17%'],
            ['code' => 'HS-HC11-HT', 'label' => 'Heures complementaires 11% non exo'],
            ['code' => 'HS-HC12-HT', 'label' => 'Heures complementaires 12% non exo'],
            ['code' => 'HS-HC17-HT', 'label' => 'Heures complementaires 17% non exo'],
            ['code' => 'HS-HC50', 'label' => 'Heures complementaires 50%'],
            ['code' => 'HS-HC50-AS', 'label' => 'Heures complementaires/astreinte 50%'],
            ['code' => 'HS-HC100', 'label' => 'Heures complementaires 100%'],
            ['code' => 'HS-HC100-HT', 'label' => 'Heures complementaires 100% non exo'],
            ['code' => 'HS-HE', 'label' => 'Heures d\'equivalence'],
            ['code' => 'HS-HE25', 'label' => 'Heures d\'equivalence majorees 25%'],
            ['code' => 'HS-HS00', 'label' => 'Heures supplementaires non majorees'],
            ['code' => 'HS-HS00RR', 'label' => 'Heures supp. non majorees remplacees/repos'],
            ['code' => 'HS-HS00-HT', 'label' => 'Heures supplementaires non majorees non exo'],
            ['code' => 'HS-HS00DP', 'label' => 'Heures de derogation permanente'],
            ['code' => 'HS-HS00DP-HT', 'label' => 'Heures de derogation permanente non exo'],
            ['code' => 'HS-HS00-AS', 'label' => 'Heures supplementaires/astreinte'],
            ['code' => 'HS-HS10', 'label' => 'Heures supplementaires 10%'],
            ['code' => 'HS-HS10R', 'label' => 'Heures supp 10% bonifiees en repos'],
            ['code' => 'HS-HS10RR', 'label' => 'Heures supp 10% remplacees/repos'],
            ['code' => 'HS-HS10-HT', 'label' => 'Heures supplementaires 10% non exonerees'],
            ['code' => 'HS-HS12', 'label' => 'Heures supplementaires 12%'],
            ['code' => 'HS-HS10RX', 'label' => 'Heures sup 10% bonifiees sans repos'],
            ['code' => 'HS-HS15', 'label' => 'Heures supplementaires 15%'],
            ['code' => 'HS-HS15R', 'label' => 'Heures supp 15% bonifiees en repos'],
            ['code' => 'HS-HS15RR', 'label' => 'Heures supp 15% remplacees/repos'],
            ['code' => 'HS-HS15-HT', 'label' => 'Heures supplementaires 15% non exonerees'],
            ['code' => 'HS-HS15RX', 'label' => 'Heures sup 15% bonifiees sans repos'],
            ['code' => 'HS-HS20', 'label' => 'Heures supplementaires 20%'],
            ['code' => 'HS-HS20R', 'label' => 'Heures supp 20% bonifiees en repos'],
            ['code' => 'HS-HS20RR', 'label' => 'Heures supp 20% remplacees/repos'],
            ['code' => 'HS-HS20-HT', 'label' => 'Heures supplementaires 20% non exonerees'],
            ['code' => 'HS-HS20RX', 'label' => 'Heures sup 20% bonifiees sans repos'],
            ['code' => 'HS-HS25', 'label' => 'Heures supplementaires 25%'],
            ['code' => 'HS-HS25R', 'label' => 'Heures supp 25% bonifiees en repos'],
            ['code' => 'HS-HS25RR', 'label' => 'Heures supp 25% remplacees/repos'],
            ['code' => 'HS-HS25-HT', 'label' => 'Heures supplementaires 25% non exonerees'],
            ['code' => 'HS-HS30', 'label' => 'Heures supplementaires 30%'],
            ['code' => 'HS-HS30R', 'label' => 'Heures supp 30% bonifiees en repos'],
            ['code' => 'HS-HS30RR', 'label' => 'Heures supp 30% remplacees/repos'],
            ['code' => 'HS-HS25-AS', 'label' => 'Heures supplementaires/astreinte 25%'],
            ['code' => 'HS-HS25R-HT', 'label' => 'Heures supp 25% bonifiees en repos non exo'],
            ['code' => 'HS-HS25DP-HT', 'label' => 'Heures de derogation permanente 25% non exo'],
            ['code' => 'HS-HS25DP', 'label' => 'Heures de derogation permanente 25%'],
            ['code' => 'HS-HS25R2', 'label' => 'Heures supplementaires 25% repos/especes'],
            ['code' => 'HS-HS20-AS', 'label' => 'Heures supplementaires/astreinte 20%'],
            ['code' => 'HS-HS25R2-HT', 'label' => 'Heures supp 25% repos/especes non exo'],
            ['code' => 'HS-HS30-HT', 'label' => 'Heures supplementaires 30% non exonerees'],
            ['code' => 'HS-HS25RX', 'label' => 'Heures supp 25% bonifiees sans repos'],
            ['code' => 'HS-HS30RX', 'label' => 'Heures supp 30% bonifiees sans repos'],
            ['code' => 'HS-HS33RX', 'label' => 'Heures supp 33% bonifiees sans repos'],
            ['code' => 'HS-HS33', 'label' => 'Heures supplementaires 33%'],
            ['code' => 'HS-HS33R', 'label' => 'Heures supp 33% bonifiees en repos'],
            ['code' => 'HS-HS33RR', 'label' => 'Heures supp 33% remplacees/repos'],
            ['code' => 'HS-HS33-HT', 'label' => 'Heures supplementaires 33% non exonerees'],
            ['code' => 'HS-HS35', 'label' => 'Heures supplementaires 35%'],
            ['code' => 'HS-HS50', 'label' => 'Heures supplementaires 50%'],
            ['code' => 'HS-HS50R', 'label' => 'Heures supp 50% bonifiees en repos'],
            ['code' => 'HS-HS50RR', 'label' => 'Heures supp 50% remplacees/repos'],
            ['code' => 'HS-HS50-HT', 'label' => 'Heures supplementaires 50% non exonerees'],
            ['code' => 'HS-HS50-AS', 'label' => 'Heures supplementaires/astreinte 50%'],
            ['code' => 'HS-HS50R2', 'label' => 'Heures supplementaires 50% repos/especes'],
            ['code' => 'HS-HS50R2-HT', 'label' => 'Heures supp 50% repos/especes non exo'],
            ['code' => 'HS-HS50RX', 'label' => 'Heures supp 50% bonifiees sans repos'],
            ['code' => 'HS-HS50DP', 'label' => 'Heures de derogation permanente 50%'],
            ['code' => 'HS-HS50DP-HT', 'label' => 'Heures de derogation permanente 50% non exo'],
            ['code' => 'HS-HS50-D', 'label' => 'Heures supplementaire 50% Dimanche'],
            ['code' => 'HS-HS75', 'label' => 'Heures supplementaire dimanche/ferie 75%'],
            ['code' => 'HS-HS100', 'label' => 'Heures supplementaire 100%'],
            ['code' => 'HS-HS100R', 'label' => 'Heures supp 100% bonifiees en repos'],
            ['code' => 'HS-HS100RR', 'label' => 'Heures supp 100% remplacees/repos'],
            ['code' => 'HS-HS100-HT', 'label' => 'Heures supplementaires 100% non exonerees'],
            ['code' => 'HS-HS100-D', 'label' => 'Heures supplementaires dimanche 100%'],
            ['code' => 'HS-HS100RX', 'label' => 'Heures supp 100% bonifiees sans repos'],
            ['code' => 'HS-HS125', 'label' => 'Heures supplementaires 125%'],
            ['code' => 'HS-HS175', 'label' => 'Heures supplementaires 175%'],
            ['code' => 'HS-HS200', 'label' => 'Heures supplementaires 200%'],
            ['code' => 'HS-HRTT', 'label' => 'Heures effectuees compensees / RTT'],
            ['code' => 'HS-HC25AN', 'label' => 'Heures complementaires annualisees 25%'],
            ['code' => 'HS-HC10AN', 'label' => 'Heures complementaires annualisees 10%'],
            ['code' => 'HS-HCAN', 'label' => 'Heures complementaires annualisees'],
            ['code' => 'HS-HS10AN', 'label' => 'Heures supplementaires annualisees 10%'],
            ['code' => 'HS-HS15AN', 'label' => 'Heures supplementaires annualisees 15%'],
            ['code' => 'HS-HS20AN', 'label' => 'Heures supplementaires annualisees 20%'],
            ['code' => 'HS-HS25AN', 'label' => 'Heures supplementaires annualisees 25%'],
            ['code' => 'HS-HS33AN', 'label' => 'Heures supplementaires annualisees 33%'],
            ['code' => 'HS-HS50AN', 'label' => 'Heures supplementaires annualisees 50%']
        ];

        foreach ($hours as $hour){
            Hour::create($hour);
        }

    }
}
