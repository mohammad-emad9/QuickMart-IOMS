            <section class="report-panel report-panel--wide" aria-labelledby="recentOrdersHeading">
                <header class="panel-heading panel-heading--action">
                    <div>
                        <p class="section-kicker">Activity log</p>
                        <h2 id="recentOrdersHeading">Recent orders</h2>
                    </div>
                    <a class="reports-link" href="orders.php">
                        <span>View all orders</span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M5 12h14m-6-6 6 6-6 6" />
                        </svg>
                    </a>
                </header>
                <div class="table-scroll">
                    <table class="reports-table reports-table--cards reports-table--orders">
                        <caption class="visually-hidden">Ten most recent orders</caption>
                        <thead>
                            <tr>
                                <th scope="col">Order ID</th>
                                <th scope="col">Type</th>
                                <th scope="col">Staff</th>
                                <th scope="col">Customer or supplier</th>
                                <th scope="col">Amount</th>
                                <th scope="col">Date</th>
                            </tr>
                        </thead>
                        <tbody id="recentOrdersBody">
                            <tr class="table-state-row">
                                <td colspan="6">Loading report data…</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </section>
