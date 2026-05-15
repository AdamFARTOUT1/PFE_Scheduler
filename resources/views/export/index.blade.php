@extends('layout.app')

@section('titre', 'Export')

@section('contenu')

<div class="container">

    <div style="text-align: center; margin-bottom: 30px;">
        <h1 class="titre-page" style="border-bottom: none;">Gestion des Soutenances PFE</h1>
        <p style="color: #7f8c8d;">ENSA Al-Hoceima — 2025/2026</p>
    </div>

    {{-- ÉTAPE 1 : Choisir le document --}}
    <div style="margin-bottom: 25px;">
        <h3 style="color: #666; margin-bottom: 15px;">
            <span style="display: inline-block; background: #2d6abf; color: #fff; border-radius: 50%; width: 28px; height: 28px; text-align: center; line-height: 28px; font-size: 14px; margin-right: 8px;">1</span>
            Choisir le document à générer
        </h3>

        <div class="grid" style="grid-template-columns: repeat(3, 1fr);">

            {{-- Carte : Affectation --}}
            <div class="panel" id="card-affectation" onclick="selectDoc('affectation')"
                style="cursor: pointer; text-align: center; transition: all 0.2s; border: 2px solid transparent;">
                <div style="font-size: 40px; margin-bottom: 10px;">📋</div>
                <h4 style="font-weight: bold;">Affectation</h4>
                <p style="color: #999; font-size: 13px;">Répartition des étudiants par encadrant</p>
                <span class="badge" id="badge-affectation" style="background: #999; color: #fff; padding: 4px 10px; border-radius: 10px; font-size: 12px;">
                    Non sélectionné
                </span>
            </div>

            {{-- Carte : Planning --}}
            <div class="panel" id="card-planning" onclick="selectDoc('planning')"
                style="cursor: pointer; text-align: center; transition: all 0.2s; border: 2px solid transparent;">
                <div style="font-size: 40px; margin-bottom: 10px;">📅</div>
                <h4 style="font-weight: bold;">Planning</h4>
                <p style="color: #999; font-size: 13px;">Calendrier complet des soutenances</p>
                <span class="badge" id="badge-planning" style="background: #999; color: #fff; padding: 4px 10px; border-radius: 10px; font-size: 12px;">
                    Non sélectionné
                </span>
            </div>

            {{-- Carte : PVs --}}
            <div class="panel" id="card-pv" onclick="selectDoc('pv')"
                style="cursor: pointer; text-align: center; transition: all 0.2s; border: 2px solid transparent;">
                <div style="font-size: 40px; margin-bottom: 10px;">📝</div>
                <h4 style="font-weight: bold;">PVs d'évaluation</h4>
                <p style="color: #999; font-size: 13px;">Fiches d'évaluation par étudiant</p>
                <span class="badge" id="badge-pv" style="background: #999; color: #fff; padding: 4px 10px; border-radius: 10px; font-size: 12px;">
                    Non sélectionné
                </span>
            </div>

        </div>
    </div>

    {{-- ÉTAPE 2 : Choisir le format (caché par défaut) --}}
    <div id="format-section" style="display: none;">
        <hr>
        <div style="margin-bottom: 25px;">
            <h3 style="color: #666; margin-bottom: 15px;">
                <span style="display: inline-block; background: #2d6abf; color: #fff; border-radius: 50%; width: 28px; height: 28px; text-align: center; line-height: 28px; font-size: 14px; margin-right: 8px;">2</span>
                Choisir le format de téléchargement
            </h3>

            <div id="format-choices" style="display: flex; gap: 20px; margin-left: 10px;">

                {{-- Option PDF --}}
                <label style="cursor: pointer; font-weight: bold;">
                    <input type="radio" name="format" id="format-pdf" value="pdf" style="margin-right: 5px;">
                    PDF
                </label>

                {{-- Option DOCX --}}
                <label style="cursor: pointer; font-weight: bold;">
                    <input type="radio" name="format" id="format-docx" value="docx" style="margin-right: 5px;">
                    DOCX
                </label>

            </div>

            {{-- Info si PVs sélectionné --}}
            <div id="pv-info" class="alert alert-info" style="display:none; margin-top: 15px;">
                Les PVs sont générés uniquement en format <strong>DOCX</strong>
                et téléchargés dans un fichier <strong>ZIP</strong>.
            </div>

        </div>

        {{-- ÉTAPE 3 : Bouton Générer --}}
        <div>
            <h3 style="color: #666; margin-bottom: 15px;">
                <span style="display: inline-block; background: #2d6abf; color: #fff; border-radius: 50%; width: 28px; height: 28px; text-align: center; line-height: 28px; font-size: 14px; margin-right: 8px;">3</span>
                Générer et télécharger
            </h3>

            <button class="btn-generer" id="btn-generer" onclick="generer()" disabled
                    style="padding: 12px 35px; font-size: 16px; opacity: 0.6;">
                Générer & Télécharger
            </button>

            {{-- Spinner --}}
            <div id="spinner" style="display:none; margin-top: 15px;">
                <span style="color: #999;">⏳ Génération en cours, veuillez patienter...</span>
            </div>
        </div>
    </div>

</div>

@endsection

@section('scripts')
<script>var BASE_URL = "{{ url('/') }}";</script>
<script src="{{ asset('js/export.js') }}"></script>
@endsection
