import SwiftUI
import WebKit

/// Stare partajată între SwiftUI și WKWebView (încărcare, eroare, reload).
final class WebViewModel: ObservableObject {
    @Published var isLoading = false
    @Published var loadFailed = false
    weak var webView: WKWebView?

    func reload() {
        loadFailed = false
        if let webView {
            webView.reload()
        }
    }
}

/// Împachetează un WKWebView care încarcă aplicația web live.
struct WebView: UIViewRepresentable {
    let url: URL
    @ObservedObject var model: WebViewModel

    func makeCoordinator() -> Coordinator {
        Coordinator(model: model, appHost: url.host)
    }

    func makeUIView(context: Context) -> WKWebView {
        let configuration = WKWebViewConfiguration()
        configuration.allowsInlineMediaPlayback = true

        let webView = WKWebView(frame: .zero, configuration: configuration)
        webView.navigationDelegate = context.coordinator
        webView.uiDelegate = context.coordinator
        webView.allowsBackForwardNavigationGestures = true

        // Pull-to-refresh nativ.
        let refresh = UIRefreshControl()
        refresh.addTarget(context.coordinator, action: #selector(Coordinator.handleRefresh(_:)), for: .valueChanged)
        webView.scrollView.refreshControl = refresh

        model.webView = webView
        webView.load(URLRequest(url: url))
        return webView
    }

    func updateUIView(_ uiView: WKWebView, context: Context) {}

    final class Coordinator: NSObject, WKNavigationDelegate, WKUIDelegate {
        private let model: WebViewModel
        private let appHost: String?

        init(model: WebViewModel, appHost: String?) {
            self.model = model
            self.appHost = appHost
        }

        @objc func handleRefresh(_ sender: UIRefreshControl) {
            model.webView?.reload()
        }

        // --- Stare încărcare -------------------------------------------------
        func webView(_ webView: WKWebView, didStartProvisionalNavigation navigation: WKNavigation!) {
            model.isLoading = true
            model.loadFailed = false
        }

        func webView(_ webView: WKWebView, didFinish navigation: WKNavigation!) {
            model.isLoading = false
            webView.scrollView.refreshControl?.endRefreshing()
        }

        func webView(_ webView: WKWebView, didFail navigation: WKNavigation!, withError error: Error) {
            finishWithError(webView, error)
        }

        func webView(_ webView: WKWebView, didFailProvisionalNavigation navigation: WKNavigation!, withError error: Error) {
            finishWithError(webView, error)
        }

        private func finishWithError(_ webView: WKWebView, _ error: Error) {
            model.isLoading = false
            webView.scrollView.refreshControl?.endRefreshing()
            // Codul -999 (NSURLErrorCancelled) apare la navigări întrerupte — nu e o eroare reală.
            if (error as NSError).code != NSURLErrorCancelled {
                model.loadFailed = true
            }
        }

        // --- Linkuri externe deschise în Safari ------------------------------
        func webView(_ webView: WKWebView,
                     decidePolicyFor navigationAction: WKNavigationAction,
                     decisionHandler: @escaping (WKNavigationActionPolicy) -> Void) {
            if let target = navigationAction.request.url,
               let host = target.host,
               let appHost,
               !host.hasSuffix(appHost),
               navigationAction.navigationType == .linkActivated {
                UIApplication.shared.open(target)
                decisionHandler(.cancel)
                return
            }
            decisionHandler(.allow)
        }

        // Linkurile cu target="_blank" se deschid în același WebView.
        func webView(_ webView: WKWebView,
                     createWebViewWith configuration: WKWebViewConfiguration,
                     for navigationAction: WKNavigationAction,
                     windowFeatures: WKWindowFeatures) -> WKWebView? {
            if navigationAction.targetFrame == nil, let target = navigationAction.request.url {
                webView.load(URLRequest(url: target))
            }
            return nil
        }
    }
}
