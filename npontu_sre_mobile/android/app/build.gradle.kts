plugins {
    id("com.android.application")
    // The Flutter Gradle Plugin must be applied after the Android and Kotlin Gradle plugins.
    id("dev.flutter.flutter-gradle-plugin")
}

android {
    namespace = "com.npontu.sre.npontu_sre_mobile"
    compileSdk = flutter.compileSdkVersion
    ndkVersion = flutter.ndkVersion

    compileOptions {
        sourceCompatibility = JavaVersion.VERSION_17
        targetCompatibility = JavaVersion.VERSION_17
    }

    defaultConfig {
        applicationId = "com.npontu.sre.npontu_sre_mobile"
        minSdk = flutter.minSdkVersion
        targetSdk = flutter.targetSdkVersion
        // Uses the version code from pubspec.yaml. When using split APKs, 1000 * ABI_VERSION
        // is added automatically by Flutter.
        versionCode = flutter.versionCode
        versionName = flutter.versionName
    }

    buildTypes {
        release {
            // TODO: Replace with a proper signing config before production release.
            // Signing with the debug keys for CI/testing. Production releases should
            // use a keystore-backed signing config.
            signingConfig = signingConfigs.getByName("debug")

            // ── Size Optimisations ──────────────────────────────────────────
            // R8 full-mode: dead code elimination + bytecode rewriting.
            // Reduces DEX size by 30–50% for typical Flutter apps.
            isMinifyEnabled = true

            // Strip unused resources (images, layouts, strings, etc.)
            // Works together with isMinifyEnabled.
            isShrinkResources = true

            // ProGuard / R8 rules. Flutter's own rules are bundled via the
            // Flutter Gradle plugin; we add project-specific additions here.
            proguardFiles(
                getDefaultProguardFile("proguard-android-optimize.txt"),
                "proguard-rules.pro"
            )
        }

        debug {
            // No minification in debug — keeps build fast and symbols readable.
            isMinifyEnabled = false
            isShrinkResources = false
        }
    }

    // ── Split APKs by ABI ───────────────────────────────────────────────────
    // This is the SINGLE BIGGEST SIZE REDUCTION. A fat APK bundles all three
    // ABI slices (arm64-v8a, armeabi-v7a, x86_64) into one file (~150-160 MB).
    // Splitting produces one APK per ABI (~35-55 MB each).
    // For production, prefer AAB (.aab) which Play Store splits per device.
    splits {
        abi {
            isEnable = true
            reset()
            // Include only the ABIs relevant to real-world Android devices.
            // x86/x86_64 are only needed for emulators — exclude from production.
            include("arm64-v8a", "armeabi-v7a", "x86_64")
            isUniversalApk = false // set to true to also emit a fat APK (for testing)
        }
    }

    // ── Native Library Packaging ─────────────────────────────────────────────
    // Extract native .so files at install time (not at runtime from the APK).
    // Required by Android 6.0+ for efficient loading; reduces installed size.
    packagingOptions {
        jniLibs {
            useLegacyPackaging = false
        }
    }
}

kotlin {
    compilerOptions {
        jvmTarget = org.jetbrains.kotlin.gradle.dsl.JvmTarget.JVM_17
    }
}

flutter {
    source = "../.."
}
