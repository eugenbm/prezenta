import SwiftUI

/// Culoarea de brand Salvamont (#841821), folosită pentru bara de status.
extension Color {
    static let brand = Color(red: 0x84 / 255, green: 0x18 / 255, blue: 0x21 / 255)
}

struct ContentView: View {
    /// URL-ul aplicației web live. Schimbă-l aici dacă se mută adresa.
    private let startURL = URL(string: "https://salvamontzarnesti.ro/registru/public/")!

    @StateObject private var model = WebViewModel()

    var body: some View {
        ZStack {
            // Fundal de brand vizibil sub bara de status / notch.
            Color.brand.ignoresSafeArea()

            WebView(url: startURL, model: model)
                .ignoresSafeArea(edges: .bottom)

            if model.isLoading && !model.loadFailed {
                ProgressView()
                    .progressViewStyle(.circular)
                    .tint(.brand)
                    .scaleEffect(1.4)
            }

            if model.loadFailed {
                ErrorOverlay { model.reload() }
            }
        }
    }
}

/// Ecran afișat când nu există conexiune sau pagina nu se poate încărca.
private struct ErrorOverlay: View {
    let onRetry: () -> Void

    var body: some View {
        VStack(spacing: 16) {
            Image(systemName: "wifi.exclamationmark")
                .font(.system(size: 44))
                .foregroundStyle(.secondary)
            Text("Nu s-a putut încărca aplicația")
                .font(.headline)
            Text("Verifică conexiunea la internet și încearcă din nou.")
                .font(.subheadline)
                .foregroundStyle(.secondary)
                .multilineTextAlignment(.center)
            Button(action: onRetry) {
                Text("Reîncearcă")
                    .fontWeight(.semibold)
                    .padding(.horizontal, 24)
                    .padding(.vertical, 10)
            }
            .buttonStyle(.borderedProminent)
            .tint(.brand)
        }
        .padding(32)
        .frame(maxWidth: .infinity, maxHeight: .infinity)
        .background(Color(.systemBackground))
    }
}
